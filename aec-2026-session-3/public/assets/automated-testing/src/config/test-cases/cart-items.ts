import { differenceInSeconds, parseISO } from "date-fns";
import { pickInvalidId, pickValidId } from "../../utils/pick-id";
import type { TestCase } from "../../utils/test-runner";
import type { TestData } from "../test-setup";
import { expiresAtCases } from "./cart";
import { data } from "./data";
import {
  createCart,
  createItem,
  restockBook,
  getCart,
} from "../../utils/fetch-utils";
import { dRandom } from "../../utils/deterministic-random";

export const cartItems: TestCase<TestData>[] = [
  // POST
  {
    name: "Add new item to cart",
    description: "POST /api/cart/:id/items returns item",
    points: 0.2,
    execute: async ({ api, expect, testData }) => {
      for (const { expected, expires_in } of expiresAtCases(testData.seed)) {
        const cartId = await createCart(api, 30);

        const book_id = pickValidId(data.books, testData.seed);
        await restockBook(api, book_id);
        const quantity = dRandom(testData.seed + "ngda", 1, 30);
        const result = await api.post(
          `/api/cart/${cartId}/items`,
          expires_in === undefined
            ? {
                book_id,
                quantity,
              }
            : {
                book_id,
                quantity,
                expires_in,
              },
        );
        expect.toHaveStatus(result, 200);

        expect.toHaveKey(result.body, "id", "Item must have id");
        expect.toEqual(result.body.cart_id, cartId, "Item must have cart_id");
        expect.toEqual(result.body.book_id, book_id, "Item must have book_id");
        expect.toEqual(
          result.body.quantity,
          quantity,
          "Item must have quantity",
        );
        expect.toHaveKey(
          result.body,
          "cart_expires_at",
          "Response must have cart_expires_at",
        );
        if (
          Math.abs(
            differenceInSeconds(
              parseISO(result.body.cart_expires_at),
              new Date(),
            ) - expected,
          ) > 5
        ) {
          throw new Error(`Must expire in ~${expected}s`);
        }
      }
    },
  },
  {
    name: "Add new item to cart",
    description: "POST /api/cart/:id/items stores item",
    points: 0.2,
    execute: async ({ api, expect, testData }) => {
      for (const { expected, expires_in } of expiresAtCases(testData.seed)) {
        const cartId = await createCart(api, 30);

        const book_id = pickValidId(data.books, testData.seed);
        await restockBook(api, book_id);
        const quantity = dRandom(testData.seed + "asdfomg", 1, 30);
        const result = await api.post(
          `/api/cart/${cartId}/items`,
          expires_in === undefined
            ? {
                book_id,
                quantity,
              }
            : {
                book_id,
                quantity,
                expires_in,
              },
        );
        expect.toHaveStatus(result, 200);
        const now = new Date();

        const cart = await getCart(api, cartId);
        expect.toExtend(
          cart.body,
          {
            id: cartId,
            items: [
              {
                id: result.body.id,
                book_id,
                quantity,
              },
            ],
            total_items: quantity,
          },
          "Cart must contain the created item",
        );
        expect.toHaveKey(
          cart.body,
          "expires_at",
          "Cart must have expires_at (GET /cart/:id)",
        );
        if (
          Math.abs(
            differenceInSeconds(parseISO(cart.body.expires_at), now) - expected,
          ) > 5
        ) {
          throw new Error(`Cart must expire in ~${expected}s`);
        }
      }
    },
  },
  {
    name: "Add same item to cart",
    description:
      "POST /api/cart/:id/items increments the quantity if the book is already in the cart",
    points: 0.2,
    execute: async ({ api, expect, testData }) => {
      const cartId = await createCart(api, 30);

      const book_id = pickValidId(data.books, testData.seed);
      await restockBook(api, book_id);
      const quantity = dRandom(testData.seed + "orrs", 1, 30);
      await api.post(`/api/cart/${cartId}/items`, {
        book_id,
        quantity,
      });
      const result = await api.post(`/api/cart/${cartId}/items`, {
        book_id,
        quantity,
      });
      expect.toHaveStatus(result, 200);
      expect.toEqual(
        result.body.quantity,
        2 * quantity,
        "Item quantity must have been incremented",
      );
    },
  },
  {
    name: "Invalid item creation",
    description: "POST /api/cart/:id/items with invalid data returns 422",
    points: 0.2,
    execute: async ({ api, expect }) => {
      const cartId = await createCart(api, 30);
      await Promise.all(
        invalidCartItems.map((item) =>
          expect.toFailWithStatus(
            () => api.post(`/api/cart/${cartId}/items`, item.data),
            422,
            item.message,
          ),
        ),
      );
    },
  },
  {
    name: "Add item to expired cart",
    description: "POST /api/cart/:id/items returns 409 for expired cart",
    points: 0.2,
    execute: async ({ api, expect, testData }) => {
      const cartId = await createCart(api, 0);
      const book_id = pickValidId(data.books, testData.seed);
      await expect.toFailWithStatus(
        () =>
          api.post(`/api/cart/${cartId}/items`, {
            book_id,
            quantity: 2,
          }),
        409,
        `expired cart`,
      );
    },
  },
  {
    name: "Non-existent cart",
    description:
      "POST /api/cart/:id/items returns 404 for non-existing cart IDs",
    points: 0.1,
    execute: async ({ api, expect, testData }) => {
      await Promise.all(
        [
          pickInvalidId([], "text", testData.seed),
          pickInvalidId([], "uuid", testData.seed),
        ].map((invalidId) =>
          expect.toFailWithStatus(
            () =>
              api.post(`/api/cart/${invalidId}/items`, {
                book_id: 1,
                quantity: 1,
              }),
            404,
            `non-existent cart with id ${invalidId}`,
          ),
        ),
      );
    },
  },
  {
    name: "Non-existent book",
    description:
      "POST /api/cart/:id/items returns 404 for non-existing book IDs",
    points: 0.1,
    execute: async ({ api, expect, testData }) => {
      const cartId = await createCart(api, 20);
      const invalidId = pickInvalidId(data.books, "number", testData.seed);
      await expect.toFailWithStatus(
        () =>
          api.post(`/api/cart/${cartId}/items`, {
            book_id: invalidId,
            quantity: 1,
          }),
        404,
        `non-existent book with id ${invalidId}`,
      );
    },
  },
  // PATCH
  {
    name: "Update item quantity",
    description: "PATCH /api/cart/:id/items/:itemId returns item",
    points: 0.2,
    execute: async ({ api, expect, testData }) => {
      for (const { expected, expires_in } of expiresAtCases(testData.seed)) {
        const cartId = await createCart(api, 30);
        const { itemId, book_id } = await createItem(
          testData.seed,
          api,
          cartId,
          30,
        );

        const quantity = dRandom(testData.seed + "asdfffg", 2, 30);
        const result = await api.patch(
          `/api/cart/${cartId}/items/${itemId}`,
          expires_in === undefined ? { quantity } : { quantity, expires_in },
        );
        expect.toHaveStatus(result, 200);

        expect.toHaveKey(result.body, "id", "Item must have id");
        expect.toEqual(result.body.cart_id, cartId, "Item must have cart_id");
        expect.toEqual(result.body.book_id, book_id, "Item must have book_id");
        expect.toEqual(
          result.body.quantity,
          quantity,
          "Item must have quantity",
        );
        expect.toHaveKey(
          result.body,
          "cart_expires_at",
          "Response must have cart_expires_at",
        );
        if (
          Math.abs(
            differenceInSeconds(
              parseISO(result.body.cart_expires_at),
              new Date(),
            ) - expected,
          ) > 5
        ) {
          throw new Error(`Cart must expire in ~${expected}s`);
        }
      }
    },
  },
  {
    name: "Update item quantity",
    description: "PATCH /api/cart/:id/items/:itemId stores quantity",
    points: 0.2,
    execute: async ({ api, expect, testData }) => {
      for (const { expected, expires_in } of expiresAtCases(testData.seed)) {
        const cartId = await createCart(api, 30);
        const { itemId, book_id } = await createItem(
          testData.seed,
          api,
          cartId,
          30,
        );

        const quantity = dRandom(testData.seed + "afomg", 2, 30);
        const result = await api.patch(
          `/api/cart/${cartId}/items/${itemId}`,
          expires_in === undefined ? { quantity } : { quantity, expires_in },
        );
        expect.toHaveStatus(result, 200);
        const now = new Date();

        const cart = await getCart(api, cartId);
        expect.toExtend(
          cart.body,
          {
            id: cartId,
            items: [
              {
                id: result.body.id,
                book_id,
                quantity,
              },
            ],
            total_items: quantity,
          },
          "Cart must contain the updated item",
        );
        expect.toHaveKey(
          cart.body,
          "expires_at",
          "Cart must have expires_at (GET /cart/:id)",
        );
        if (
          Math.abs(
            differenceInSeconds(parseISO(cart.body.expires_at), now) - expected,
          ) > 5
        ) {
          throw new Error(`Cart must expire in ~${expected}s`);
        }
      }
    },
  },
  {
    name: "Invalid item update",
    description:
      "PATCH /api/cart/:id/items/:itemId with invalid data returns 422",
    points: 0.2,
    execute: async ({ api, expect, testData }) => {
      const cartId = await createCart(api, 30);
      const { itemId } = await createItem(testData.seed, api, cartId, 30);
      await Promise.all(
        invalidCartItemUpdates.map((item) =>
          expect.toFailWithStatus(
            () => api.patch(`/api/cart/${cartId}/items/${itemId}`, item.data),
            422,
            item.message,
          ),
        ),
      );
    },
  },
  {
    name: "Update item in expired cart",
    description:
      "PATCH /api/cart/:id/items/:itemId returns 409 for expired cart",
    points: 0.2,
    execute: async ({ api, expect, testData }) => {
      const cartId = await createCart(api, 30);
      const { itemId } = await createItem(testData.seed, api, cartId, 0);
      await expect.toFailWithStatus(
        () => api.patch(`/api/cart/${cartId}/items/${itemId}`, { quantity: 2 }),
        409,
        `expired cart`,
      );
    },
  },
  {
    name: "Update item in non-existent cart",
    description:
      "PATCH /api/cart/:id/items/:itemId returns 404 for non-existing cart",
    points: 0.1,
    execute: async ({ api, expect, testData }) => {
      await Promise.all(
        [
          pickInvalidId([], "text", testData.seed),
          pickInvalidId([], "uuid", testData.seed),
        ].map((invalidCartId) =>
          expect.toFailWithStatus(
            () =>
              api.patch(`/api/cart/${invalidCartId}/items/item-id`, {
                quantity: 2,
              }),
            404,
            `non-existent cart with id ${invalidCartId}`,
          ),
        ),
      );
    },
  },
  {
    name: "Update non-existent item in cart",
    description:
      "PATCH /api/cart/:id/items/:itemId returns 404 for non-existing cart",
    points: 0.1,
    execute: async ({ api, expect, testData }) => {
      const cartId = await createCart(api, 30);
      await Promise.all(
        [
          pickInvalidId([], "text", testData.seed),
          pickInvalidId([], "uuid", testData.seed),
        ].map((invalidId) =>
          expect.toFailWithStatus(
            () =>
              api.patch(`/api/cart/${cartId}/items/${invalidId}`, {
                quantity: 2,
              }),
            404,
            `non-existent item with id ${invalidId}`,
          ),
        ),
      );
    },
  },
  // DELETE
  {
    name: "Remove item",
    description: "DELETE /api/cart/:id/items/:itemId returns item",
    points: 0.2,
    execute: async ({ api, expect, testData }) => {
      for (const { expected, expires_in } of expiresAtCases(testData.seed)) {
        const cartId = await createCart(api, 30);
        const { itemId, book_id } = await createItem(
          testData.seed,
          api,
          cartId,
          30,
        );

        const result = await api.delete(
          `/api/cart/${cartId}/items/${itemId}`,
          expires_in === undefined ? {} : { expires_in },
        );
        expect.toHaveStatus(result, 200);

        expect.toHaveKey(result.body, "id", "Item must have id");
        expect.toEqual(result.body.cart_id, cartId, "Item must have cart_id");
        expect.toEqual(result.body.book_id, book_id, "Item must have book_id");
        expect.toHaveKey(
          result.body,
          "cart_expires_at",
          "Response must have cart_expires_at",
        );
        if (
          Math.abs(
            differenceInSeconds(
              parseISO(result.body.cart_expires_at),
              new Date(),
            ) - expected,
          ) > 5
        ) {
          throw new Error(`Cart must expire in ~${expected}s`);
        }
      }
    },
  },
  {
    name: "Remove item",
    description: "DELETE /api/cart/:id/items/:itemId deletes item from storage",
    points: 0.2,
    execute: async ({ api, expect, testData }) => {
      for (const { expected, expires_in } of expiresAtCases(testData.seed)) {
        const cartId = await createCart(api, 30);
        const { itemId } = await createItem(testData.seed, api, cartId, 30);

        const result = await api.delete(
          `/api/cart/${cartId}/items/${itemId}`,
          expires_in === undefined ? {} : { expires_in },
        );
        expect.toHaveStatus(result, 200);
        const now = new Date();

        const cart = await getCart(api, cartId);
        expect.toExtend(
          cart.body,
          {
            id: cartId,
            items: [],
            total_items: 0,
          },
          "Cart must be empty",
        );
        expect.toHaveKey(
          cart.body,
          "expires_at",
          "Cart must have expires_at (GET /cart/:id)",
        );
        if (
          Math.abs(
            differenceInSeconds(parseISO(cart.body.expires_at), now) - expected,
          ) > 5
        ) {
          throw new Error(`Cart must expire in ~${expected}s`);
        }
      }
    },
  },
  {
    name: "Invalid item deletion",
    description:
      "DELETE /api/cart/:id/items/:itemId with invalid data returns 422",
    points: 0.2,
    execute: async ({ api, expect, testData }) => {
      const cartId = await createCart(api, 30);
      const { itemId } = await createItem(testData.seed, api, cartId, 30);
      await Promise.all(
        invalidDeletes.map((item) =>
          expect.toFailWithStatus(
            () => api.delete(`/api/cart/${cartId}/items/${itemId}`, item.data),
            422,
            item.message,
          ),
        ),
      );
    },
  },
  {
    name: "Remove item from expired cart",
    description:
      "DELETE /api/cart/:id/items/:itemId returns 409 for expired cart",
    points: 0.2,
    execute: async ({ api, expect, testData }) => {
      const cartId = await createCart(api, 30);
      const { itemId } = await createItem(testData.seed, api, cartId, 0);
      await expect.toFailWithStatus(
        () => api.delete(`/api/cart/${cartId}/items/${itemId}`, {}),
        409,
        `expired cart`,
      );
    },
  },
  {
    name: "Remove item from non-existent cart",
    description:
      "DELETE /api/cart/:id/items/:itemId returns 404 for non-existing cart",
    points: 0.1,
    execute: async ({ api, expect, testData }) => {
      await Promise.all(
        [
          pickInvalidId([], "text", testData.seed),
          pickInvalidId([], "uuid", testData.seed),
        ].map((invalidCartId) =>
          expect.toFailWithStatus(
            () => api.delete(`/api/cart/${invalidCartId}/items/item-id`, {}),
            404,
            `non-existent cart with id ${invalidCartId}`,
          ),
        ),
      );
    },
  },
  {
    name: "Remove non-existent item from cart",
    description:
      "DELETE /api/cart/:id/items/:itemId returns 404 for non-existing cart",
    points: 0.1,
    execute: async ({ api, expect, testData }) => {
      const cartId = await createCart(api, 30);
      for (const invalidId of [
        pickInvalidId([], "text", testData.seed),
        pickInvalidId([], "uuid", testData.seed),
      ]) {
        await expect.toFailWithStatus(
          () => api.delete(`/api/cart/${cartId}/items/${invalidId}`, {}),
          404,
          `non-existent item with id ${invalidId}`,
        );
      }
    },
  },
];

const invalidCartItems = [
  {
    data: { quantity: 1 },
    message: "missing book_id",
  },
  {
    data: { book_id: 1 },
    message: "missing quantity",
  },
  {
    data: { book_id: 1, quantity: 0 },
    message: "zero quantity",
  },
  {
    data: { book_id: 1, quantity: -5 },
    message: "negative quantity",
  },
  {
    data: { quantity: 20000, book_id: 1 },
    message: "insufficient stock",
  },
];

const invalidCartItemUpdates = [
  {
    data: { quantity: 20000 },
    message: "insufficient stock",
  },
  {
    data: { quantity: 0 },
    message: "zero quantity on update",
  },
  {
    data: { quantity: -3 },
    message: "negative quantity on update",
  },
  {
    data: {},
    message: "missing quantity on update",
  },
];

const invalidDeletes = [
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
