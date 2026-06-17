import type { TestResult } from "./test-runner";

export interface TestMetrics {
  totalTests: number;
  passedTests: number;
  failedTests: number;
  totalPoints: number;
  earnedPoints: number;
}

export function calculateMetrics(
  results: TestResult[],
  testCases: { points: number }[],
): TestMetrics {
  const totalTests = testCases.length;
  const passedTests = results.filter((r) => r.status === "passed").length;
  const totalPoints = testCases.reduce((sum, t) => sum + t.points, 0);
  const earnedPointsRaw = results.reduce(
    (sum, result, i) =>
      sum + (result.status === "passed" ? (testCases[i]?.points ?? 0) : 0),
    0,
  );
  const earnedPoints = Math.round(100 * earnedPointsRaw) / 100;

  return {
    totalTests,
    passedTests,
    failedTests: totalTests - passedTests,
    totalPoints,
    earnedPoints,
  };
}
