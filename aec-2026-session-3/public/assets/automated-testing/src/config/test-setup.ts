import { randomUUID } from "crypto";
import { FetchClient } from "../utils/fetch-client";

// ---------------------------------------------------------------------------
// TestData
//
// Created fresh before every test run so each run uses unique data —
// this prevents competitors from hard-coding responses.
// ---------------------------------------------------------------------------

export interface TestData {
  seed: string;
}

export const setup =
  (fixedSeed?: string) =>
  async (externalApi: FetchClient): Promise<TestData> => {
    const seed = fixedSeed ?? randomUUID();
    const response = await externalApi.post("/api/hidden/seed", {
      seed,
    });

    return {
      seed: response.body.seed,
    };
  };

export async function teardown(
  externalApi: FetchClient,
  data: TestData,
): Promise<void> {}
