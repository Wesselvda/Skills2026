import { createClient } from "redis";
import { competitors } from "../config/competitors";
import type { Competitor } from "../config/competitors";
import type { TestMetrics } from "./metrics";

export interface CompetitorResult {
  competitor: Competitor;
  metrics: TestMetrics;
}

export interface Leaderboard {
  competitorResults: CompetitorResult[];
  highestScore: number;
  totalTestRuns: number;
  maxPoints: number;
}

// --- Redis singleton ---

let client: ReturnType<typeof createClient> | null = null;

async function getClient() {
  if (!client) {
    client = createClient({
      url: process.env.REDIS_URL ?? "redis://localhost:6379",
    });
    await client.connect();
  }
  return client;
}

// --- Keys ---

const keys = {
  competitor: (ip: string) => `competitor:${ip}`,
  totalRuns: "leaderboard:totalRuns",
};

// --- Public API ---

const emptyMetrics: TestMetrics = {
  totalTests: 0,
  passedTests: 0,
  failedTests: 0,
  totalPoints: 0,
  earnedPoints: 0,
};

export async function getLeaderboard(maxPoints: number): Promise<Leaderboard> {
  const redis = await getClient();

  const competitorResults = await Promise.all(
    competitors.map(async (competitor) => {
      const raw = await redis.get(keys.competitor(competitor.ipAddress));
      const metrics: TestMetrics = raw
        ? (JSON.parse(raw) as TestMetrics)
        : { ...emptyMetrics };
      return { competitor, metrics };
    }),
  );

  competitorResults.sort(
    (a, b) => b.metrics.earnedPoints - a.metrics.earnedPoints,
  );

  const totalRuns = await redis.get(keys.totalRuns);
  const highestScore = competitorResults.reduce(
    (max, r) => Math.max(max, r.metrics.earnedPoints),
    0,
  );

  return {
    competitorResults,
    highestScore,
    totalTestRuns: totalRuns ? parseInt(totalRuns) : 0,
    maxPoints,
  };
}

export async function saveResults(
  competitor: Competitor,
  metrics: TestMetrics,
): Promise<void> {
  const redis = await getClient();
  await redis.set(
    keys.competitor(competitor.ipAddress),
    JSON.stringify(metrics),
  );
  await redis.incr(keys.totalRuns);
}
