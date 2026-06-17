import { Hono } from "hono";
import { serveStatic } from "@hono/node-server/serve-static";
import { testCases } from "./config/test-cases";
import { setup, teardown } from "./config/test-setup";
import { competitors } from "./config/competitors";
import { FetchClient } from "./utils/fetch-client";
import { TestRunner } from "./utils/test-runner";
import { calculateMetrics } from "./utils/metrics";
import { getLeaderboard, saveResults } from "./utils/leaderboard";
import { getRequestIp } from "./utils/ip";
import { TesterPage } from "./views/tester";
import { LeaderboardPage } from "./views/leaderboard";
import { serve } from "@hono/node-server";
import z from "zod";
import { ExternalApiClient } from "./utils/external-api-client";

const app = new Hono();

// ---------------------------------------------------------------------------
// Static files
// ---------------------------------------------------------------------------

app.use("/assets/*", serveStatic({ root: "./" }));
app.use("/public/*", serveStatic({ root: "./" }));

// ---------------------------------------------------------------------------
// Pages
// ---------------------------------------------------------------------------

app.get("/", (c) => c.html(<TesterPage />));

app.get("/leaderboard", (c) => c.html(<LeaderboardPage />));

// ---------------------------------------------------------------------------
// API
// ---------------------------------------------------------------------------
app.post("/api/run-tests", async (c) => {
  const targetSchema = z.object({
    checkProduction: z.boolean().default(false),
    port: z.coerce.number().int().min(1).max(65535).default(8080),
  });

  const parsedTarget = targetSchema.safeParse(await c.req.json());
  if (!parsedTarget.success) {
    return c.json(
      {
        error: z.prettifyError(parsedTarget.error),
      },
      400,
    );
  }

  // Resolve competitor IP — OVERRIDE_IP is useful for local development
  // where requests don't come through a reverse proxy.
  const ip = process.env.OVERRIDE_IP ?? getRequestIp(c.req.raw.headers);
  if (!ip) {
    return c.json(
      {
        error:
          "Could not determine request IP. " +
          "Set OVERRIDE_IP for local development or ensure x-forwarded-for is present.",
      },
      400,
    );
  }

  const competitor = competitors.find((it) => it.ipAddress === ip);
  if (!competitor) {
    return c.json({ error: `No competitor registered for IP ${ip}` }, 403);
  }

  const target = parsedTarget.data.checkProduction
    ? `http://c${competitor.seat}-s3.17.skills.local`
    : `http://${competitor.ipAddress}:${parsedTarget.data.port}`;

  const api = new FetchClient(target);
  const externalApi = new ExternalApiClient(
    process.env.OVERRIDE_API ??
      `http://c${competitor.seat}-s3-api.17.skills.local`,
  );
  const runner = new TestRunner(externalApi, api, testCases, setup(), teardown);

  const results = await runner.run();
  const metrics = calculateMetrics(results, testCases);
  await saveResults(competitor, metrics);

  return c.json({ results, metrics });
});

app.post("/api/generate-marks", async (c) => {
  const results = await Promise.all(
    competitors.map(async (competitor) => {
      const api = new FetchClient(
        `http://c${competitor.seat}-s3.17.skills.local`,
      );
      const externalApi = new ExternalApiClient(
        process.env.OVERRIDE_API ??
          `http://c${competitor.seat}-s3-api.17.skills.local`,
      );
      const runner = new TestRunner(
        externalApi,
        api,
        testCases,
        setup("marking"),
        teardown,
      );

      const results = await runner.run();
      const metrics = calculateMetrics(results, testCases);
      return { competitor, results, metrics };
    }),
  );
  return c.json(results);
});

app.get("/api/leaderboard", async (c) => {
  const maxPoints =
    Math.round(100 * testCases.reduce((sum, t) => sum + t.points, 0)) / 100;
  const leaderboard = await getLeaderboard(maxPoints);
  return c.json(leaderboard);
});

app.get("/api/test-cases", (c) => {
  const meta = testCases.map((tc) => ({
    name: tc.name,
    description: tc.description,
    points: tc.points,
  }));
  return c.json(meta);
});

// ---------------------------------------------------------------------------
// Error handler
// ---------------------------------------------------------------------------

app.onError((err, c) => {
  console.error(err);
  return c.json({ error: err.message }, 300);
});

serve({
  fetch: app.fetch,
  serverOptions: {
    requestTimeout: 5 * 60 * 1000, // 5 minutes
    headersTimeout: 5 * 60 * 1000, // must be > requestTimeout
    keepAliveTimeout: 65 * 1000,
  },
  port: 3000,
});
