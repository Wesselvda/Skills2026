import { differenceInSeconds, isWithinInterval, parseISO } from "date-fns";
import { pickInvalidId, pickValidId } from "../../utils/pick-id";
import type { TestCase } from "../../utils/test-runner";
import type { TestData } from "../test-setup";
import { sleep } from "../../utils/sleep";
import { data } from "./data";
import { checkCover } from "./books";
import {
  createCart,
  createItem,
  getStock,
  restockBook,
} from "../../utils/fetch-utils";
import { dRandom } from "../../utils/deterministic-random";

export const cart: TestCase<TestData>[] = [
  {
    name: "Create cart",
    description: "POST /api/cart creates cart",
    points: 0.3,
    execute: async ({ api, expect, testData }) => {
      for (const { expected, expires_in } of expiresAtCases(testData.seed)) {
        const result = await api.post(
          "/api/cart",
          expires_in === undefined ? {} : { expires_in },
        );
        expect.toHaveStatus(result, 200);
        expect.toHaveKey(result.body, "id", "Response must have id");
        expect.toHaveKey(
          result.body,
          "expires_at",
          "Response must have expires_at",
        );
        if (
          Math.abs(
            differenceInSeconds(parseISO(result.body.expires_at), new Date()) -
              expected,
          ) > 5
        ) {
          throw new Error(`Must expire in ~${expected}s`);
        }
      }
    },
  },
  {
    name: "Invalid cart creation",
    description: "POST /api/cart with invalid data returns 422",
    points: 0.2,
    execute: async ({ api, expect }) => {
      await Promise.all(
        invalidCarts.map((cart) =>
          expect.toFailWithStatus(
            () => api.post("/api/cart", cart.data),
            422,
            cart.message,
          ),
        ),
      );
    },
  },
  {
    name: "Retrieve cart",
    description: "GET /api/cart/:id returns newly created cart",
    points: 0.2,
    execute: async ({ api, expect, testData }) => {
      for (const { expected, expires_in } of expiresAtCases(testData.seed)) {
        const cartId = await createCart(api, expires_in);
        const result = await api.get(`/api/cart/${cartId}`);
        expect.toHaveStatus(result, 200);
        expect.toExtend(
          result.body,
          {
            id: cartId,
            expired: false,
            items: [],
            total: 0,
            discounted: 0,
            total_items: 0,
          },
          "Expected empty cart",
        );
        expect.toHaveKey(
          result.body,
          "expires_at",
          "Response must have expires_at",
        );
        if (
          Math.abs(
            differenceInSeconds(parseISO(result.body.expires_at), new Date()) -
              expected,
          ) > 5
        ) {
          throw new Error(`Must expire in ~${expected}s`);
        }
      }
    },
  },
  {
    name: "Retrieve cart",
    description: "GET /api/cart/:id returns expired cart",
    points: 0.2,
    execute: async ({ api, expect }) => {
      const cartId = await createCart(api, 0);
      const result = await api.get(`/api/cart/${cartId}`);
      expect.toHaveStatus(result, 200);
      expect.toExtend(
        result.body,
        {
          id: cartId,
          expired: true,
          items: [],
          total: 0,
          discounted: 0,
          total_items: 0,
        },
        "Expected empty expired cart",
      );
    },
  },
  {
    name: "Retrieve non-empty cart without localization",
    description: "GET /api/cart/:id returns 200 with one book",
    points: 0.3,
    execute: async ({ api, expect, testData }) => {
      const cartId = await createCart(api, 30);
      const { itemId, book_id } = await createItem(
        testData.seed,
        api,
        cartId,
        30,
      );
      const book = data.books.find((b) => b.id === book_id)!;
      const stock = await getStock(api, book_id);

      const result = await api.get(`/api/cart/${cartId}`);
      expect.toHaveStatus(result, 200);
      expect.toExtend(
        result.body,
        {
          items: [
            {
              id: itemId,
              book_id: book_id,
              title: {
                value: book.title,
                fallback: false,
              },
              unit_price: book.price,
              quantity: 1,
              total_price: book.price,
              stock,
            },
          ],
          total_items: 1,
          total: book.price,
        },
        "Must contain one book",
      );
    },
  },
  {
    name: "Retrieve non-empty cart without localization",
    description: "GET /api/cart/:id response contains valid link to book cover",
    points: 0.3,
    execute: async ({ api, expect, testData }) => {
      const cartId = await createCart(api, 30);
      const { book_id } = await createItem(testData.seed, api, cartId, 30);
      const book = data.books.find((b) => b.id === book_id)!;

      const result = await api.get(`/api/cart/${cartId}`);
      expect.toHaveStatus(result, 200);

      if (!result.body?.items?.[0]) {
        throw new Error("Must contain one book");
      }
      await checkCover(expect, api, result.body.items[0], book.cover_image);
    },
  },
  {
    name: "Retrieve non-empty cart with localization",
    description:
      "GET /api/cart/:id response contains the correct language metadata",
    points: 0.1,
    execute: async ({ api, expect, testData }) => {
      const cartId = await createCart(api, 30);
      const { book_id } = await createItem(testData.seed, api, cartId, 30);
      const book = data.books.find((b) => b.id === book_id)!;

      for (const locale of data.parsedLocales) {
        const result = await api.get(`/api/cart/${cartId}`, locale.header);
        expect.toHaveStatus(result, 200);
        expect.toExtend(
          result.body,
          {
            items: [
              {
                original_language: book.original_language,
                translated_language:
                  book.original_language === locale.expected
                    ? null
                    : locale.expected,
              },
            ],
          },
          "Must return the correct language metadata",
        );
      }
    },
  },
  {
    name: "Retrieve non-empty cart with localization",
    description:
      "GET /api/cart/:id response is localized with the translation service",
    points: 0.4,
    execute: async ({ api, expect, externalApi, testData }) => {
      const cartId = await createCart(api, 30);
      const { book_id } = await createItem(testData.seed, api, cartId, 30);
      const book = data.books.find((b) => b.id === book_id)!;

      for (const locale of data.locales) {
        const result = await api.get(`/api/cart/${cartId}`, locale);
        expect.toHaveStatus(result, 200);
        const doNotTranslate = locale === book.original_language;
        const translations = await externalApi.batchTranslateTexts(
          {
            texts: [book.title].map((text) => ({
              text,
              sourceLanguage: book.original_language,
              targetLanguage: locale,
            })),
          },
          doNotTranslate,
        );
        expect.toExtend(
          result.body,
          {
            items: [
              {
                title: {
                  value: translations[0]!.translation ?? book.title,
                  fallback: !translations[0]!.success,
                },
              },
            ],
          },
          "Must translate the fields",
        );
      }
    },
  },
  {
    name: "Calculate discounted price",
    description:
      "GET /api/cart/:id response has the discounts applied correctly",
    points: 0.5,
    execute: async ({ api, expect, externalApi, testData }) => {
      const cartId = await createCart(api, 30);
      const items: Record<number, number> = {};
      for (let i = 0; i < 5; i++) {
        const { book_id } = await createItem(testData.seed, api, cartId, 30);
        const quantity = items[book_id] ?? 0;
        items[book_id] = quantity + 1;
      }

      const cartItems = Object.entries(items).map(([bookId, quantity]) => ({
        bookId,
        quantity,
        price: data.books.find((b) => b.id === Number(bookId))!.price,
      }));
      const total = cartItems.reduce(
        (total, item) => total + item.price * item.quantity,
        0,
      );
      const total_items = cartItems.reduce(
        (total, item) => total + item.quantity,
        0,
      );
      const discounts = await externalApi.getDiscountRules();
      let discounted = total;
      const applied_discounts: { name: string; id: string }[] = [];
      for (const { conditions, discount, id, name } of discounts) {
        if (
          (conditions.minQuantity === null ||
            total_items >= conditions.minQuantity) &&
          (conditions.minTotalPrice === null ||
            total >= conditions.minTotalPrice) &&
          (conditions.timespan === null ||
            isWithinInterval(new Date(), {
              start: parseISO(conditions.timespan.start),
              end: parseISO(conditions.timespan.end),
            }))
        ) {
          if (discount.type === "percentage") {
            discounted = ((100 - discount.value) / 100) * discounted;
          } else {
            discounted -= discount.value;
          }
          applied_discounts.push({ id, name });
        }
      }

      const result = await api.get(`/api/cart/${cartId}`);
      expect.toHaveStatus(result, 200);
      expect.toExtend(
        result.body,
        {
          total,
          discounted: Math.round(100 * discounted) / 100,
          total_items,
          applied_discounts,
        },
        "Expected " +
          discounted +
          ", but got " +
          result.body.discounted +
          " as the discounted price",
      );
    },
  },
  {
    name: "Cart not found",
    description:
      "GET /api/cart/:id returns 404 for non-existing or invalid cart IDs",
    points: 0.1,
    execute: async ({ api, expect, testData }) => {
      await Promise.all(
        [
          pickInvalidId([], "text", testData.seed),
          pickInvalidId([], "uuid", testData.seed),
        ].map((invalidId) =>
          expect.toFailWithStatus(
            () => api.get(`/api/cart/${invalidId}`),
            404,
            `non-existent cart with id ${invalidId}`,
          ),
        ),
      );
    },
  },
  {
    name: "Item reservation",
    description: "Adding an item to the cart reserves it",
    points: 0.3,
    execute: async ({ api, expect, testData }) => {
      const cartId = await createCart(api, 30);

      const book_id = pickValidId(data.books, testData.seed);
      await restockBook(api, book_id);
      const originalStock = await getStock(api, book_id);

      const quantity = dRandom(testData.seed + "mg", 1, 30);
      const result = await api.post(`/api/cart/${cartId}/items`, {
        book_id,
        quantity,
        expires_in: 30,
      });
      expect.toHaveStatus(result, 200);

      const newStock = await getStock(api, book_id);
      expect.toEqual(
        originalStock - newStock,
        quantity,
        "Stock must decrease after reservation",
      );
    },
  },
  {
    name: "Reservations expire",
    description: "The stock becomes available again if the cart expires",
    points: 0.5,
    execute: async ({ api, expect, testData }) => {
      const cartId = await createCart(api, 30);

      const book_id = pickValidId(data.books, testData.seed);
      await restockBook(api, book_id);
      const originalStock = await getStock(api, book_id);

      const quantity = dRandom(testData.seed + "alomg", 1, 30);
      const result = await api.post(`/api/cart/${cartId}/items`, {
        book_id,
        quantity,
        expires_in: 1,
      });
      expect.toHaveStatus(result, 200);
      await sleep(1.2);

      const newStock = await getStock(api, book_id);
      expect.toEqual(
        originalStock,
        newStock,
        "Stock must be the same as before after expiration",
      );
    },
  },
];

const invalidCarts = [
  {
    data: { expires_in: 301 },
    message: "expiry above maximum",
  },
  {
    data: { expires_in: -10 },
    message: "negative expiry",
  },
  {
    data: { expires_in: "abc" as any },
    message: "invalid expiry type",
  },
];

export function expiresAtCases(seed: string) {
  const randomExpiry = dRandom(seed, 10, 30);
  return [
    {
      expected: 300,
      expires_in: undefined,
    },
    {
      expected: randomExpiry,
      expires_in: randomExpiry,
    },
  ];
}
