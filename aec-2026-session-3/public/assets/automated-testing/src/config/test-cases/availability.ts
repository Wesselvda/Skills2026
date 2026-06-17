import { EventSource } from "eventsource";
import { pickInvalidId } from "../../utils/pick-id";
import type { TestCase } from "../../utils/test-runner";
import type { TestData } from "../test-setup";
import { data } from "./data";

export const availability: TestCase<TestData>[] = [
  {
    name: "Valid book availability",
    description: "GET /api/books/1/availability returns text/event-stream",
    points: 0.2,
    parallel: true,
    execute: async ({ api, expect }) => {
      const controller = new AbortController();
      const timeout = setTimeout(() => controller.abort(), 10000);

      try {
        const response = await fetch(
          `${api.baseUrl}/api/books/1/availability`,
          {
            signal: controller.signal,
          },
        );

        expect.toHaveStatus({ status: response.status } as any, 200);
        const contentType = response.headers.get("content-type");
        if (!contentType || !contentType.includes("text/event-stream")) {
          throw new Error(`Expected text/event-stream, got ${contentType}`);
        }
      } finally {
        clearTimeout(timeout);
      }
    },
  },
  {
    name: "Valid book availability",
    description: "GET /api/books/1/availability sends list of stores",
    points: 0.5,
    parallel: true,
    execute: async ({ api, expect, externalApi }) => {
      let timeout: NodeJS.Timeout;
      const stores = await new Promise((res, rej) => {
        const es = new EventSource(`${api.baseUrl}/api/books/1/availability`);
        timeout = setTimeout(() => {
          es.close();
          rej(new Error("Did not receive a stores event"));
        }, 20000);
        es.addEventListener("stores", (event) => {
          es.close();
          res(JSON.parse(event.data));
        });
        es.addEventListener("error", () => {
          es.close();
          rej(new Error());
        });
      })
        .catch(() => {
          throw new Error("Stream must send stores event");
        })
        .finally(() => {
          clearTimeout(timeout);
        });

      const referenceStores = await externalApi.getStoresForBook("1");
      expect.toDeepEqual(
        stores,
        referenceStores.map((s) => ({
          store_id: s.storeId,
          name: s.name,
        })),
        "Stores event data is incorrect",
      );
    },
  },
  {
    name: "Valid book availability",
    description: "GET /api/books/1/availability sends list of availabilities",
    points: 0.5,
    parallel: true,
    execute: async ({ api, expect, externalApi }) => {
      const referenceStores = await externalApi.getStoresForBook("1");
      const availabilitiesPromise = Promise.allSettled(
        referenceStores.map(async (s) => {
          const availability = await externalApi.getBookAvailabilityAtStore(
            s.storeId.toString(),
            "1",
          );
          return availability;
        }),
      );

      let timeout: NodeJS.Timeout;
      const availabilities = await new Promise((res, rej) => {
        const availabilities: {
          storeId: number;
          availability: string;
          inventory?: number;
        }[] = [];
        const es = new EventSource(`${api.baseUrl}/api/books/1/availability`);
        timeout = setTimeout(() => {
          es.close();
          res(availabilities);
        }, 20000);
        es.addEventListener("availability", (event) => {
          availabilities.push({
            inventory: undefined,
            ...JSON.parse(event.data),
          });
        });
        es.addEventListener("error", () => {
          es.close();
          res(availabilities);
        });
      })
        .catch(() => {
          throw new Error("Stream must send availability events");
        })
        .finally(() => {
          clearTimeout(timeout);
        });

      const referenceAvailabilities = (await availabilitiesPromise)
        .filter((a) => a.status === "fulfilled")
        .map((s) => ({
          store_id: s.value.storeId,
          availability: s.value.availability,
          inventory: s.value.inventory ?? null,
        }))
        .sort((a, b) => a.store_id - b.store_id);
      (availabilities as { storeId: number }[]).sort(
        (a, b) => a.storeId - b.storeId,
      );
      expect.toDeepEqual(
        availabilities,
        referenceAvailabilities,
        "Some availability event was incorrect",
      );
    },
  },
  {
    name: "Non-existent book availability",
    description:
      "GET /api/books/:id/availability for non-existent books returns 404",
    points: 0.1,
    parallel: true,
    execute: async ({ api, expect, testData }) => {
      await Promise.all(
        [
          pickInvalidId(data.books, "number", testData.seed),
          "3a",
          pickInvalidId(data.books, "text", testData.seed),
          pickInvalidId(data.books, "uuid", testData.seed),
        ].map((invalidId) =>
          expect.toFailWithStatus(
            () => api.get(`/api/authors/${invalidId}/availability`),
            404,
            `non-existent book with id ${invalidId}`,
          ),
        ),
      );
    },
  },
];
