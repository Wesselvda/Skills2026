/** @jsxImportSource hono/jsx/dom */
import { useState, useEffect } from "hono/jsx";
import { render } from "hono/jsx/dom";

interface TestCaseMeta {
  name: string;
  description: string;
  points: number;
}

interface TestResult {
  status: "passed" | "failed";
  duration: number;
  message?: string;
}

interface RunResponse {
  results: TestResult[];
  metrics: {
    totalTests: number;
    passedTests: number;
    failedTests: number;
    totalPoints: number;
    earnedPoints: number;
  };
}

function StatusIcon({ result }: { result?: TestResult }) {
  if (!result) return <span class="test-status-icon pending">·</span>;
  if (result.status === "passed")
    return <span class="test-status-icon pass">✓</span>;
  return <span class="test-status-icon fail">✗</span>;
}

function TesterApp() {
  const [testCases, setTestCases] = useState<TestCaseMeta[]>([]);
  const [results, setResults] = useState<TestResult[] | null>(null);
  const [metrics, setMetrics] = useState<RunResponse["metrics"] | null>(null);
  const [error, setError] = useState<string | null>(null);
  const [loading, setLoading] = useState(false);
  const [checkProduction, setCheckProduction] = useState(false);
  const [port, setPort] = useState(8080);

  useEffect(() => {
    fetch("/api/test-cases")
      .then((res) => res.json())
      .then(setTestCases)
      .catch(() => setError("Failed to load test cases."));
  }, []);

  const runTests = async () => {
    setLoading(true);
    setError(null);
    setResults(null);
    setMetrics(null);

    try {
      const res = await fetch("/api/run-tests", {
        method: "POST",
        headers: {
          Accept: "application/json",
          "Content-Type": "application/json",
        },
        body: JSON.stringify({ checkProduction, port }),
      });
      const data = await res.json();

      if (!res.ok) {
        setError(data.error ?? "An unknown error occurred.");
        return;
      }

      const response = data as RunResponse;
      setResults(response.results);
      setMetrics(response.metrics);
    } catch (err) {
      setError(err instanceof Error ? err.message : "Request failed.");
    } finally {
      setLoading(false);
    }
  };

  return (
    <div>
      <header class="tester-header">
        <div>
          <div class="tester-title">API Tester</div>
          <div class="tester-subtitle">
            Albert Einstein Cup 2026 · Skill 17 · Session 3
          </div>
        </div>
        <form
          class="tester-form"
          onSubmit={(e) => {
            e.preventDefault();
            runTests();
          }}
        >
          <label class="checkbox-label">
            <input
              type="checkbox"
              class="tester-checkbox"
              checked={checkProduction}
              onChange={(e) => setCheckProduction(e.currentTarget.checked)}
            />
            Check Production
          </label>
          <label class="port-label">
            Port:
            <input
              disabled={checkProduction}
              type="number"
              class="tester-port"
              value={port}
              onChange={(e) => setPort(Number(e.currentTarget.value))}
              min={1}
              max={65535}
            />
          </label>
          <button type="submit" class="run-btn" disabled={loading}>
            {loading ? "▶  Running..." : "▶  Run Tests"}
          </button>
        </form>
      </header>

      {error && <div class="error-box">{error}</div>}

      {metrics && (
        <div class="metrics-bar">
          <div class="metric">
            <span class="metric-label">Passed</span>
            <span
              class={`metric-value${metrics.passedTests === metrics.totalTests ? " pass" : ""}`}
            >
              {metrics.passedTests}/{metrics.totalTests}
            </span>
          </div>
          <div class="metrics-divider" />
          <div class="metric">
            <span class="metric-label">Score</span>
            <span class="metric-value score">
              {metrics.earnedPoints.toFixed(2)}/{metrics.totalPoints.toFixed(2)}
            </span>
          </div>
          <div class="metrics-divider" />
          <div class="metric">
            <span class="metric-label">Failed</span>
            <span class="metric-value">{metrics.failedTests}</span>
          </div>
        </div>
      )}

      {loading && <p class="loading-hint">executing test suite</p>}

      <ul class="test-list">
        {testCases.map((tc, i) => {
          const result = results?.[i];
          const statusClass = result
            ? result.status === "passed"
              ? "passed"
              : "failed"
            : "";

          return (
            <li key={i} class={`test-item ${statusClass}`}>
              <StatusIcon result={result} />
              <div class="test-info">
                <div class="test-name">{tc.name}</div>
                <div class="test-desc">{tc.description}</div>
                {result?.status === "failed" && result.message && (
                  <div class="test-result-msg">{result.message}</div>
                )}
              </div>
              <div class="test-meta">
                <span class="test-points">{tc.points} pt</span>
                {result && (
                  <span class="test-duration">{result.duration}ms</span>
                )}
              </div>
            </li>
          );
        })}
      </ul>
    </div>
  );
}

render(<TesterApp />, document.getElementById("tester-root")!);
