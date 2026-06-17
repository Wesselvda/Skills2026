/** @jsxImportSource hono/jsx/dom */
import { useState, useEffect } from "hono/jsx";
import { render } from "hono/jsx/dom";

interface TestMetrics {
  totalTests: number;
  passedTests: number;
  failedTests: number;
  totalPoints: number;
  earnedPoints: number;
}

interface CompetitorResult {
  competitor: { name: string };
  metrics: TestMetrics;
}

interface Leaderboard {
  competitorResults: CompetitorResult[];
  highestScore: number;
  totalTestRuns: number;
  maxPoints: number;
}

function RankBadge({ rank }: { rank: number }) {
  const cls =
    rank === 1
      ? "rank-badge gold"
      : rank === 2
        ? "rank-badge silver"
        : rank === 3
          ? "rank-badge bronze"
          : "rank-badge other";
  return <div class={cls}>{rank}</div>;
}

function Stats({ data }: { data: Leaderboard | null }) {
  return (
    <div class="lb-stats">
      <div class="lb-stat">
        <div class="lb-stat-value">
          {data ? data.competitorResults.length : "—"}
        </div>
        <div class="lb-stat-label">Competitors</div>
      </div>
      <div class="lb-stat">
        <div class="lb-stat-value">{data ? data.totalTestRuns : "—"}</div>
        <div class="lb-stat-label">Submissions</div>
      </div>
      <div class="lb-stat">
        <div class="lb-stat-value">
          {data ? data.highestScore.toFixed(2) : "—"}
        </div>
        <div class="lb-stat-label">Top Score</div>
      </div>
      <div class="lb-stat">
        <div class="lb-stat-value">
          {data ? data.maxPoints.toFixed(2) : "—"}
        </div>
        <div class="lb-stat-label">Max Score</div>
      </div>
    </div>
  );
}

function LeaderboardApp() {
  const [data, setData] = useState<Leaderboard | null>(null);
  const [error, setError] = useState<string | null>(null);

  const fetchData = async () => {
    try {
      const res = await fetch("/api/leaderboard");
      setData(await res.json());
    } catch {
      setError("Failed to load leaderboard.");
    }
  };

  useEffect(() => {
    fetchData();
    const interval = setInterval(fetchData, 30000);
    return () => clearInterval(interval);
  }, []);

  if (error)
    return (
      <>
        <Stats data={null} />
        <div class="lb-error">{error}</div>
      </>
    );

  if (!data) return <Stats data={null} />;

  const { competitorResults } = data;

  return (
    <>
      <Stats data={data} />
      {competitorResults.length === 0 ? (
        <div class="lb-empty">No submissions yet. Be the first!</div>
      ) : (
        <div class="lb-table">
          <div class="lb-col-header">
            <div>Rank</div>
            <div>Competitor</div>
            <div>Tests Passed</div>
            <div>Score</div>
          </div>
          {competitorResults.map((item, idx) => {
            const rank = idx + 1;
            const rankClass =
              rank === 1
                ? "lb-row rank-1"
                : rank === 2
                  ? "lb-row rank-2"
                  : rank === 3
                    ? "lb-row rank-3"
                    : "lb-row";
            return (
              <div
                key={idx}
                class={rankClass}
                style={`animation-delay:${idx * 60}ms`}
              >
                <div class="lb-rank">
                  <RankBadge rank={rank} />
                </div>
                <div class="lb-name">{item.competitor.name}</div>
                <div class="lb-tests">
                  <span class="pass-count">{item.metrics.passedTests}</span>/
                  {item.metrics.totalTests}
                </div>
                <div class="lb-score">
                  {item.metrics.earnedPoints.toFixed(2)}
                </div>
              </div>
            );
          })}
        </div>
      )}
    </>
  );
}

render(<LeaderboardApp />, document.getElementById("leaderboard-root")!);
