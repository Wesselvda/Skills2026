import { type FetchResult, ApiError, FetchClient } from "./fetch-client";
import { ExternalApiClient } from "./external-api-client";
import { deepEqualWith, isMatchWith } from "./diff";

export interface TestCase<TData = unknown> {
  name: string;
  description: string;
  points: number;
  execute: (ctx: TestContext<TData>) => Promise<void>;
  parallel?: boolean;
}

export interface TestResult {
  status: "passed" | "failed";
  duration: number;
  message?: string;
}

interface TestContext<TData> {
  externalApi: ExternalApiClient;
  api: FetchClient;
  expect: ExpectApi;
  testData: TData;
}

export interface ExpectApi {
  toEqual(actual: unknown, expected: unknown, message?: string): void;
  toDeepEqual(actual: unknown, expected: unknown, message?: string): void;
  toExtend(actual: unknown, expected: object, message?: string): void;
  toHaveStatus(result: FetchResult, expected: number): void;
  toBeEmpty(actual: unknown, message?: string): void;
  toNotBeEmpty(actual: unknown, message?: string): void;
  toHaveKey(actual: unknown, key: string, message?: string): void;
  toBeArray(actual: unknown, message?: string): void;
  toFailWithStatus(
    request: () => Promise<unknown>,
    status: number,
    urlDescription: string,
  ): void;
}

export const ANY_STRING = Symbol("Any String");

export class TestRunner<TData> {
  constructor(
    private readonly externalApi: ExternalApiClient,
    private readonly api: FetchClient,
    private readonly testCases: TestCase<TData>[],
    private readonly setup: (externalApi: ExternalApiClient) => Promise<TData>,
    private readonly teardown: (
      externalApi: ExternalApiClient,
      data: TData,
    ) => Promise<void>,
  ) {}

  async run(): Promise<TestResult[]> {
    const testData = await this.setup(this.externalApi);
    try {
      const results: (TestResult | Promise<TestResult>)[] = [];
      for (const testCase of this.testCases) {
        if (testCase.parallel) {
          results.push(this.runOne(testCase, testData));
        } else {
          results.push(await this.runOne(testCase, testData));
        }
      }
      return await Promise.all(results);
    } finally {
      await this.teardown(this.externalApi, testData);
    }
  }

  private async runOne(
    testCase: TestCase<TData>,
    testData: TData,
  ): Promise<TestResult> {
    const start = Date.now();
    try {
      await testCase.execute({
        externalApi: this.externalApi,
        api: this.api,
        expect: this.createExpect(),
        testData,
      });
      return { status: "passed", duration: Date.now() - start };
    } catch (error) {
      return {
        status: "failed",
        duration: Date.now() - start,
        message: error instanceof Error ? error.message : "Unknown error",
      };
    }
  }

  private createExpect(): ExpectApi {
    return {
      toEqual(actual, expected, message) {
        if (actual !== expected) {
          throw new Error(message ?? `Expected ${expected}, got ${actual}`);
        }
      },
      toDeepEqual(actual, expected, message) {
        const comparison = deepEqualWith(actual, expected, (a, b) => {
          if (typeof b === "function") {
            return b(a);
          }
          return undefined;
        });
        if (!comparison.equal) {
          throw new Error(
            message
              ? `${message} (issue at ${comparison.path})`
              : `Expected ${JSON.stringify(expected)}, got ${JSON.stringify(actual)}`,
          );
        }
      },
      toExtend(actual, expected, message) {
        const comparison = isMatchWith(actual, expected, (a, b) => {
          if (typeof b === "function") {
            return b(a);
          }
          return undefined;
        });
        if (!comparison.equal) {
          throw new Error(
            message
              ? `${message} (issue at ${comparison.path})`
              : `Expected ${JSON.stringify(actual)} to have the values in ${JSON.stringify(expected)}`,
          );
        }
      },
      toHaveStatus(result, expected) {
        if (result.status !== expected) {
          throw new Error(`Expected status ${expected}, got ${result.status}`);
        }
      },
      toBeEmpty(actual, message) {
        if (actual == null) return;
        if (Array.isArray(actual) && actual.length > 0)
          throw new Error(message ?? "Expected value to be empty");
        if (
          typeof actual === "object" &&
          Object.keys(actual as object).length > 0
        )
          throw new Error(message ?? "Expected value to be empty");
      },
      toNotBeEmpty(actual, message) {
        if (actual == null)
          throw new Error(
            message ?? `Expected value to not be empty, got ${actual}`,
          );
        if (Array.isArray(actual) && actual.length === 0)
          throw new Error(message ?? "Expected value to not be empty");
        if (
          typeof actual === "object" &&
          Object.keys(actual as object).length === 0
        )
          throw new Error(message ?? "Expected value to not be empty");
      },
      toHaveKey(actual, key, message) {
        if (
          typeof actual !== "object" ||
          actual == null ||
          !(key in (actual as object))
        ) {
          throw new Error(message ?? `Expected object to have key "${key}"`);
        }
      },
      toBeArray(actual, message) {
        if (!Array.isArray(actual)) {
          throw new Error(message ?? "Expected value to be an array");
        }
      },
      async toFailWithStatus(request, status, urlDescription) {
        try {
          await request();
          throw new Error(`Expected ${status} for ${urlDescription}, got 2xx`);
        } catch (err) {
          if (err instanceof ApiError) {
            if (err.status !== status) {
              throw new Error(
                `Expected ${status} for ${urlDescription}, got ${err.status}`,
              );
            }
          } else {
            throw err;
          }
        }
      },
    };
  }
}
