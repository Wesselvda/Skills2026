import { dRandom } from "../../utils/deterministic-random";
import { pickInvalidId } from "../../utils/pick-id";
import type { TestCase } from "../../utils/test-runner";
import type { TestData } from "../test-setup";
import { data } from "./data";

export const stock: TestCase<TestData>[] = [
  {
    name: "Valid stock update",
    description: "PATCH /api/books/1/stock with valid stock value",
    points: 0.2,
    execute: async ({ api, expect, testData }) => {
      const stock = dRandom(testData.seed + "agmg", 100, 200);
      const result = await api.patch("/api/books/1/stock", { stock });
      expect.toHaveStatus(result, 200);
      expect.toEqual(result.body.id, 1, "Response must have id");
      expect.toEqual(
        result.body.total_stock,
        stock,
        "Response must have the total stock",
      );
      if (typeof result.body.stock !== "number" || result.body.stock > stock) {
        throw new Error("Response must have the unreserved stock");
      }
    },
  },
  {
    name: "Valid stock update",
    description: "PATCH /api/books/1/stock updates stock",
    points: 0.2,
    execute: async ({ api, expect, testData }) => {
      const stock = dRandom(testData.seed + "asg", 100, 200);
      const result1 = await api.patch("/api/books/1/stock", { stock });
      expect.toHaveStatus(result1, 200);

      let before;
      try {
        before = await api.get("/api/books/1");
      } catch (e) {
        throw new Error("Unable to load the stock using GET /api/books/1");
      }

      const result2 = await api.patch("/api/books/1/stock", {
        stock: stock + 50,
      });
      expect.toHaveStatus(result2, 200);

      let diff = 0;
      try {
        const after = await api.get("/api/books/1");

        diff = after.body.stock - before.body.stock;
      } catch (e) {
        throw new Error("Unable to load the stock using GET /api/books/1");
      }

      expect.toEqual(diff, 50, "Stock did not increase by the correct amount");
    },
  },
  {
    name: "Invalid stock update",
    description:
      "PATCH /api/books/1/stock returns 422 when the stock is insufficient for the reservations",
    points: 0.2,
    execute: async ({ api, expect }) => {
      const result1 = await api.patch("/api/books/1/stock", { stock: 1000 });
      expect.toHaveStatus(result1, 200);

      let before;
      try {
        const cart = await api.post("/api/cart", {});
        await api.post(`/api/cart/${cart.body.id}/items`, {
          book_id: 1,
          quantity: 5,
        });
      } catch (e) {
        throw new Error("Unable to reserve the book");
      }

      await expect.toFailWithStatus(
        () => api.patch("/api/books/1/stock", { stock: 4 }),
        422,
        "insufficient stock",
      );
    },
  },
  {
    name: "Invalid stock update",
    description: "PATCH /api/books/1/stock with invalid data returns 422",
    points: 0.2,
    execute: async ({ api, expect }) => {
      const invalidUpdates = [
        { data: {}, message: "missing stock field" },
        { data: { stock: -5 }, message: "negative stock value" },
        { data: { stock: "abc" }, message: "string stock value" },
        { data: { stock: 10.5 }, message: "float stock value" },
      ];
      await Promise.all(
        invalidUpdates.map((update) =>
          expect.toFailWithStatus(
            () => api.patch("/api/books/1/stock", update.data),
            422,
            update.message,
          ),
        ),
      );
    },
  },
  {
    name: "Update stock of non-existing book",
    description:
      "PATCH /api/books/:id/stock returns 404 for non-existing books",
    points: 0.1,
    execute: async ({ api, expect, testData }) => {
      await Promise.all(
        [
          pickInvalidId(data.books, "number", testData.seed),
          "3a",
          pickInvalidId(data.books, "text", testData.seed),
          pickInvalidId(data.books, "uuid", testData.seed),
        ].map((invalidId) =>
          expect.toFailWithStatus(
            () =>
              api.patch(`/api/authors/${invalidId}/stock`, {
                stock: 1000,
              }),
            404,
            `non-existent book with id ${invalidId}`,
          ),
        ),
      );
    },
  },
];
