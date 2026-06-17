import type { TestCase } from "../../utils/test-runner";
import type { TestData } from "../test-setup";

export const general: TestCase<TestData>[] = [
  {
    name: "Health check",
    description: 'GET /api/health must return 200 with { "status": "ok" }',
    points: 0.5,
    parallel: true,
    execute: async ({ api, expect }) => {
      const result = await api.get("/api/health");
      expect.toHaveStatus(result, 200);
      expect.toEqual(
        result.body?.status,
        "up",
        'Response body must be { "status": "up" }',
      );
    },
  },
  {
    name: "Non-existing endpoint",
    description: "GET /api/non-existing must return 404",
    points: 0.5,
    parallel: true,
    execute: async ({ api, expect }) => {
      await expect.toFailWithStatus(
        () => api.get("/api/non-existing"),
        404,
        "non-existing endpoint",
      );
    },
  },
];
