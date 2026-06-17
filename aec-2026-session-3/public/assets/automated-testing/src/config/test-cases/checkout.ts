import {
  startCheckout,
  createCart,
  createItem,
  getStock,
} from "../../utils/fetch-utils";
import { pickInvalidId } from "../../utils/pick-id";
import { sleep } from "../../utils/sleep";
import type { TestCase } from "../../utils/test-runner";
import type { TestData } from "../test-setup";
import { } from "./cart";

export const checkout: TestCase<TestData>[] = [
  // Start
  {
    name: "Start checkout successfully",
    description: "POST /api/cart/:id/checkout/start creates payment session",
    points: 0.2,
    execute: async ({ api, expect, testData }) => {
      const cartId = await createCart(api, 30);
      await createItem(testData.seed, api, cartId, 30);
      const result = await api.post(`/api/cart/${cartId}/checkout/start`, {
        callbackUrl: "https://mysite.com/payment-done",
      });
      expect.toHaveStatus(result, 200);
      expect.toEqual(result.body.cart_id, cartId, "Response must have cart_id");
      expect.toHaveKey(
        result.body,
        "payment_url",
        "Response must have payment_url",
      );

      try {
        const { response, body } = await api.fetch(result.body.payment_url);
        expect.toHaveStatus({ status: response.status, body }, 200);
      } catch {
        throw new Error(
          `The payment URL does not work (${result.body.payment_url})`,
        );
      }
    },
  },
  {
    name: "Start checkout successfully",
    description: "POST /api/cart/:id/checkout/start reuses payment session",
    points: 0.2,
    execute: async ({ api, expect, testData }) => {
      const cartId = await createCart(api, 30);
      await createItem(testData.seed, api, cartId, 30);
      const result1 = await api.post(`/api/cart/${cartId}/checkout/start`, {
        callbackUrl: "https://mysite.com/payment-done",
      });
      const result2 = await api.post(`/api/cart/${cartId}/checkout/start`, {
        callbackUrl: "https://mysite.com/payment-done",
      });
      expect.toHaveStatus(result2, 200);
      expect.toDeepEqual(
        result1.body,
        result2.body,
        "Response must be the same on repeated request",
      );

      try {
        const { response, body } = await api.fetch(result2.body.payment_url);
        expect.toHaveStatus({ status: response.status, body }, 200);
      } catch {
        throw new Error(
          `The payment URL does not work (${result2.body.payment_url})`,
        );
      }
    },
  },
  {
    name: "Restart checkout successfully after failure",
    description:
      "POST /api/cart/:id/checkout/start creates new payment session if previous failed",
    points: 0.5,
    execute: async ({ api, expect, testData }) => {
      const cartId = await createCart(api, 30);
      await createItem(testData.seed, api, cartId, 30);
      const result = await api.post(`/api/cart/${cartId}/checkout/start`, {
        callbackUrl: "https://mysite.com/payment-done",
      });
      expect.toHaveStatus(result, 200);

      try {
        await api.fetch(result.body.payment_url + "/fail", {
          method: "POST",
          redirect: "manual",
        });
      } catch {
        throw new Error(
          `The payment URL does not work (${result.body.payment_url})`,
        );
      }

      const newResult = await api.post(`/api/cart/${cartId}/checkout/start`, {
        callbackUrl: "https://mysite.com/payment-done",
      });
      expect.toHaveStatus(newResult, 200);
      expect.toEqual(
        result.body.payment_url !== newResult.body.payment_url,
        true,
        "The new payment URL must be different",
      );

      try {
        const { response, body } = await api.fetch(newResult.body.payment_url);
        expect.toHaveStatus({ status: response.status, body }, 200);
      } catch {
        throw new Error(
          `The payment URL does not work (${newResult.body.payment_url})`,
        );
      }
    },
  },
  {
    name: "Invalid checkout start",
    description:
      "POST /api/cart/:id/checkout/start with invalid data returns 422",
    points: 0.2,
    execute: async ({ api, expect, testData }) => {
      const cartId = await createCart(api, 30);
      await createItem(testData.seed, api, cartId, 30);
      await Promise.all(
        invalidCheckoutStarts.map((checkout) =>
          expect.toFailWithStatus(
            () => api.post(`/api/cart/${cartId}/checkout/start`, checkout.data),
            422,
            checkout.message,
          ),
        ),
      );
    },
  },
  {
    name: "Start checkout with non-existent cart",
    description:
      "POST /api/cart/:id/checkout/start returns 404 for non-existing cart IDs",
    points: 0.1,
    execute: async ({ api, expect, testData }) => {
      await Promise.all(
        [
          pickInvalidId([], "text", testData.seed),
          pickInvalidId([], "uuid", testData.seed),
        ].map((invalidId) =>
          expect.toFailWithStatus(
            () =>
              api.post(`/api/cart/${invalidId}/checkout/start`, {
                callbackUrl: "https://example.com",
              }),
            404,
            `non-existent cart with id ${invalidId}`,
          ),
        ),
      );
    },
  },
  {
    name: "Start checkout with empty cart",
    description:
      "POST /api/cart/:id/checkout/start with empty cart returns 409",
    points: 0.2,
    execute: async ({ api, expect }) => {
      const cartId = await createCart(api, 30);
      await expect.toFailWithStatus(
        () =>
          api.post(`/api/cart/${cartId}/checkout/start`, {
            callbackUrl: "https://mysite.com/payment-done",
          }),
        409,
        "empty cart checkout",
      );
    },
  },
  {
    name: "Start checkout with expired cart",
    description:
      "POST /api/cart/:id/checkout/start with expired cart returns 409",
    points: 0.2,
    execute: async ({ api, expect, testData }) => {
      const cartId = await createCart(api, 30);
      await createItem(testData.seed, api, cartId, 0);
      await expect.toFailWithStatus(
        () =>
          api.post(`/api/cart/${cartId}/checkout/start`, {
            callbackUrl: "https://mysite.com/payment-done",
          }),
        409,
        "expired cart checkout",
      );
    },
  },
  // Complete
  {
    name: "Successful completion",
    description: "POST /api/cart/:id/checkout/complete returns info",
    points: 0.2,
    execute: async ({ api, expect, testData }) => {
      const cartId = await createCart(api, 30);
      await createItem(testData.seed, api, cartId, 30);
      const url = await startCheckout(api, cartId);
      try {
        await api.fetch(url + "/process", {
          method: "POST",
          redirect: "manual",
          ...paymentInfo,
        });
      } catch {
        throw new Error(`The payment URL does not work (${url})`);
      }

      const completeResult = await api.post(
        `/api/cart/${cartId}/checkout/complete`,
        {},
      );
      expect.toHaveStatus(completeResult, 200);
      expect.toHaveKey(
        completeResult.body,
        "paid_at",
        "Response must have paid_at",
      );
      expect.toEqual(
        completeResult.body.cart_id,
        cartId,
        "Response must have cart_id",
      );
    },
  },
  {
    name: "Successful completion",
    description: "POST /api/cart/:id/checkout/complete deletes the cart",
    points: 0.5,
    execute: async ({ api, expect, testData }) => {
      const cartId = await createCart(api, 30);
      const { book_id } = await createItem(testData.seed, api, cartId, 30);
      const url = await startCheckout(api, cartId);
      try {
        await api.fetch(url + "/process", {
          method: "POST",
          redirect: "manual",
          ...paymentInfo,
        });
      } catch {
        throw new Error(`The payment URL does not work (${url})`);
      }

      try {
        await api.get("/api/cart/" + cartId);
      } catch {
        throw new Error("Unable to load cart");
      }
      const stockBefore = await getStock(api, book_id);

      const completeResult = await api.post(
        `/api/cart/${cartId}/checkout/complete`,
        {},
      );
      expect.toHaveStatus(completeResult, 200);

      expect.toFailWithStatus(
        () => api.get("/cart/" + cartId),
        404,
        "deleted cart",
      );
      const stockAfter = await getStock(api, book_id);
      expect.toEqual(stockBefore, stockAfter, "Stock was not reduced");
    },
  },
  {
    name: "Complete checkout with non-existent cart",
    description:
      "POST /api/cart/:id/checkout/complete returns 404 for non-existing cart IDs",
    points: 0.1,
    execute: async ({ api, expect, testData }) => {
      await Promise.all(
        [
          pickInvalidId([], "text", testData.seed),
          pickInvalidId([], "uuid", testData.seed),
        ].map((invalidId) =>
          expect.toFailWithStatus(
            () => api.post(`/api/cart/${invalidId}/checkout/complete`, {}),
            404,
            `non-existent cart with id ${invalidId}`,
          ),
        ),
      );
    },
  },
  {
    name: "Complete checkout before starting it",
    description:
      "POST /api/cart/:id/checkout/complete before starting it returns 409",
    points: 0.2,
    execute: async ({ api, expect, testData }) => {
      const cartId = await createCart(api, 30);
      await createItem(testData.seed, api, cartId, 30);

      await expect.toFailWithStatus(
        () => api.post(`/api/cart/${cartId}/checkout/complete`, {}),
        409,
        "complete before starting",
      );
    },
  },
  {
    name: "Complete checkout before completing payment",
    description:
      "POST /api/cart/:id/checkout/complete before completing payment returns 409",
    points: 0.2,
    execute: async ({ api, expect, testData }) => {
      const cartId = await createCart(api, 30);
      await createItem(testData.seed, api, cartId, 30);
      await startCheckout(api, cartId);

      await expect.toFailWithStatus(
        () => api.post(`/api/cart/${cartId}/checkout/complete`, {}),
        409,
        "complete with pending payment",
      );
    },
  },
  {
    name: "Complete checkout with failed payment",
    description:
      "POST /api/cart/:id/checkout/complete with failed payment returns 409",
    points: 0.2,
    execute: async ({ api, expect, testData }) => {
      const cartId = await createCart(api, 30);
      await createItem(testData.seed, api, cartId, 30);
      const url = await startCheckout(api, cartId);

      try {
        await api.fetch(url + "/fail", {
          method: "POST",
          redirect: "manual",
        });
      } catch {
        throw new Error(`The payment URL does not work (${url})`);
      }

      await expect.toFailWithStatus(
        () => api.post(`/api/cart/${cartId}/checkout/complete`, {}),
        409,
        "complete with failed payment",
      );
    },
  },
  {
    name: "Complete checkout with expired cart",
    description:
      "POST /api/cart/:id/checkout/complete with expired cart returns 409",
    points: 0.2,
    execute: async ({ api, expect, testData }) => {
      const cartId = await createCart(api, 30);
      await createItem(testData.seed, api, cartId, 5);
      const sleepPromise = sleep(5.2);
      const url = await startCheckout(api, cartId);
      await sleepPromise;

      try {
        await api.fetch(url + "/process", {
          method: "POST",
          redirect: "manual",
          ...paymentInfo,
        });
      } catch {
        throw new Error(`The payment URL does not work (${url})`);
      }

      await expect.toFailWithStatus(
        () => api.post(`/api/cart/${cartId}/checkout/complete`, {}),
        409,
        "complete with expired cart",
      );
    },
  },
  {
    name: "Complete checkout with cancelled payment",
    description: "Adding an item to the cart cancels the payment",
    points: 0.4,
    execute: async ({ api, expect, testData }) => {
      const cartId = await createCart(api, 30);
      await createItem(testData.seed, api, cartId, 30);
      await startCheckout(api, cartId);
      await createItem(testData.seed, api, cartId, 30);
      await expect.toFailWithStatus(
        () => api.post(`/api/cart/${cartId}/checkout/complete`, {}),
        409,
        "complete with cancelled payment",
      );
    },
  },
  {
    name: "Complete checkout with cancelled payment",
    description: "Updating an item to the cart cancels the payment",
    points: 0.4,
    execute: async ({ api, expect, testData }) => {
      const cartId = await createCart(api, 30);
      const itemId = await createItem(testData.seed, api, cartId, 30);
      await startCheckout(api, cartId);
      await api.patch(`/api/cart/${cartId}/items/${itemId.itemId}`, {
        quantity: 1,
        expires_in: 30,
      });
      await expect.toFailWithStatus(
        () => api.post(`/api/cart/${cartId}/checkout/complete`, {}),
        409,
        "complete with cancelled payment",
      );
    },
  },
  {
    name: "Complete checkout with cancelled payment",
    description: "Removing an item from the cart cancels the payment",
    points: 0.4,
    execute: async ({ api, expect, testData }) => {
      const cartId = await createCart(api, 30);
      const itemId = await createItem(testData.seed, api, cartId, 30);
      await startCheckout(api, cartId);
      await api.delete(`/api/cart/${cartId}/items/${itemId.itemId}`, {});
      await expect.toFailWithStatus(
        () =>
          api.post(`/api/cart/${cartId}/checkout/complete`, { expires_in: 30 }),
        409,
        "complete with cancelled payment",
      );
    },
  },
];

const invalidCheckoutStarts = [
  {
    data: {},
    message: "missing callbackUrl",
  },
  {
    data: { callbackUrl: "" },
    message: "empty callbackUrl",
  },
];

const paymentInfo = {
  headers: {
    "content-type": "application/x-www-form-urlencoded",
  },
  body: `cardHolderName=Glenn&cardNumber=1234123412341234&expiryMonth=12&expiryYear=2032&cvc=123&token=asdf`,
};
