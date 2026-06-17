import type { FC } from "hono/jsx";
import { css, Style } from "hono/css";
import { Layout } from "./layout";

const gradientShift = css`
  @keyframes gradientShift {
    0% {
      background-position: 0% 50%;
    }
    50% {
      background-position: 100% 50%;
    }
    100% {
      background-position: 0% 50%;
    }
  }
`;

const fadeInUp = css`
  @keyframes fadeInUp {
    from {
      opacity: 0;
      transform: translateY(16px);
    }
    to {
      opacity: 1;
      transform: translateY(0);
    }
  }
`;

const leaderboardCss = css`
  *,
  *::before,
  *::after {
    box-sizing: border-box;
    margin: 0;
    padding: 0;
  }
  :root {
    --gold: #ffd700;
    --silver: #c0c0c0;
    --bronze: #cd7f32;
    --bg: #0a0a12;
    --surface: rgba(255, 255, 255, 0.04);
    --border: rgba(255, 255, 255, 0.08);
    --text: #f0f0ff;
    --muted: rgba(240, 240, 255, 0.45);
  }
  body {
    background: var(--bg);
    color: var(--text);
    font-family: "Inter", system-ui, sans-serif;
    min-height: 100vh;
    overflow-x: hidden;
  }
  .lb-bg {
    position: fixed;
    inset: 0;
    z-index: 0;
    background: linear-gradient(
      135deg,
      #07071a 0%,
      #0b1530 40%,
      #07071a 70%,
      #10062a 100%
    );
    background-size: 300% 300%;
    animation: ${gradientShift} 30s ease infinite;
  }
  .lb-bg::after {
    content: "";
    position: absolute;
    inset: 0;
    background-image:
      linear-gradient(rgba(99, 102, 241, 0.03) 1px, transparent 1px),
      linear-gradient(90deg, rgba(99, 102, 241, 0.03) 1px, transparent 1px);
    background-size: 48px 48px;
  }
  .lb-wrap {
    position: relative;
    z-index: 1;
    max-width: 860px;
    margin: 0 auto;
    padding: 56px 24px 80px;
  }
  .lb-header {
    text-align: center;
    margin-bottom: 64px;
  }
  .lb-eyebrow {
    font-size: 20px;
    font-weight: 600;
    letter-spacing: 3px;
    text-transform: uppercase;
    color: var(--text);
    margin-bottom: 14px;
  }
  .lb-stats {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 16px;
    margin-top: 36px;
  }
  @media (max-width: 640px) {
    .lb-stats {
      grid-template-columns: repeat(2, 1fr);
    }
  }
  .lb-stat {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 6px;
    padding: 20px 16px;
    background: rgba(255, 255, 255, 0.04);
    border: 1px solid rgba(255, 255, 255, 0.08);
    border-radius: 12px;
    backdrop-filter: blur(8px);
    -webkit-backdrop-filter: blur(8px);
  }
  .lb-stat-value {
    font-size: 32px;
    font-weight: 800;
    color: #818cf8;
    line-height: 1;
  }
  .lb-stat-label {
    font-size: 10px;
    color: var(--muted);
    letter-spacing: 1.5px;
    text-transform: uppercase;
  }
  .lb-table {
    display: flex;
    flex-direction: column;
    gap: 10px;
    margin-top: 56px;
    text-align: left;
  }
  .lb-col-header {
    display: grid;
    grid-template-columns: 64px 1fr 140px 120px;
    padding: 0 20px;
    border: 1px solid transparent;
    color: var(--muted);
    font-size: 11px;
    font-weight: 600;
    letter-spacing: 1.5px;
    text-transform: uppercase;
  }
  .lb-col-header > div:nth-child(3) {
    text-align: center;
  }
  .lb-col-header > div:nth-child(4) {
    text-align: right;
  }
  .lb-row {
    display: grid;
    grid-template-columns: 64px 1fr 140px 120px;
    align-items: center;
    padding: 18px 20px;
    background: var(--surface);
    border: 1px solid var(--border);
    border-radius: 12px;
    backdrop-filter: blur(12px);
    -webkit-backdrop-filter: blur(12px);
    transition:
      transform 0.2s,
      border-color 0.2s;
    animation: ${fadeInUp} 0.4s ease both;
  }
  .lb-row:hover {
    transform: translateX(4px);
    border-color: rgba(129, 140, 248, 0.3);
  }
  .lb-row.rank-1 {
    border-color: rgba(255, 215, 0, 0.3);
    background: linear-gradient(
      135deg,
      rgba(255, 215, 0, 0.06) 0%,
      var(--surface) 60%
    );
  }
  .lb-row.rank-2 {
    border-color: rgba(192, 192, 192, 0.25);
    background: linear-gradient(
      135deg,
      rgba(192, 192, 192, 0.05) 0%,
      var(--surface) 60%
    );
  }
  .lb-row.rank-3 {
    border-color: rgba(205, 127, 300, 0.25);
    background: linear-gradient(
      135deg,
      rgba(205, 127, 300, 0.05) 0%,
      var(--surface) 60%
    );
  }
  .lb-rank {
    display: flex;
    align-items: center;
    justify-content: flex-start;
  }
  .rank-badge {
    width: 36px;
    height: 36px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 800;
    font-size: 14px;
  }
  .rank-badge.gold {
    background: radial-gradient(circle at 35% 35%, #ffe566, #c8960a);
    color: #3a2200;
    box-shadow: 0 0 0 2px rgba(255, 215, 0, 0.3);
  }
  .rank-badge.silver {
    background: radial-gradient(circle at 35% 35%, #e8e8e8, #909090);
    color: #2a2a2a;
    box-shadow: 0 0 0 2px rgba(192, 192, 192, 0.3);
  }
  .rank-badge.bronze {
    background: radial-gradient(circle at 35% 35%, #e8a070, #8a5020);
    color: #2a1200;
    box-shadow: 0 0 0 2px rgba(205, 127, 300, 0.3);
  }
  .rank-badge.other {
    background: rgba(255, 255, 255, 0.06);
    color: var(--muted);
    border: 1px solid var(--border);
    font-size: 13px;
  }
  .lb-name {
    font-weight: 700;
    font-size: 15px;
  }
  .lb-row.rank-1 .lb-name {
    color: var(--gold);
  }
  .lb-row.rank-2 .lb-name {
    color: var(--silver);
  }
  .lb-row.rank-3 .lb-name {
    color: var(--bronze);
  }
  .lb-tests {
    font-size: 13px;
    color: var(--muted);
    font-family: "JetBrains Mono", monospace;
    text-align: center;
  }
  .lb-tests .pass-count {
    color: #34d399;
    font-weight: 600;
  }
  .lb-score {
    font-family: "JetBrains Mono", monospace;
    font-weight: 600;
    font-size: 18px;
    text-align: right;
    padding-right: 18px;
  }
  .lb-row.rank-1 .lb-score {
    color: var(--gold);
  }
  .lb-row.rank-2 .lb-score {
    color: var(--silver);
  }
  .lb-row.rank-3 .lb-score {
    color: var(--bronze);
  }
  .lb-row:not(.rank-1):not(.rank-2):not(.rank-3) .lb-score {
    color: #818cf8;
  }
  .lb-empty {
    text-align: center;
    padding: 64px 0;
    color: var(--muted);
    font-size: 15px;
  }
  .lb-error {
    text-align: center;
    padding: 40px 0;
    color: #f87171;
  }
`;

export const LeaderboardPage: FC = () => (
  <Layout title="Leaderboard" head={<Style>{leaderboardCss}</Style>}>
    <div class="lb-bg" />
    <div class="lb-wrap">
      <header class="lb-header">
        <div class="lb-eyebrow">
          Albert Einstein Cup 2026 · Skill 17 · Session 3
        </div>
        <div id="leaderboard-root" />
      </header>
    </div>
    <script type="module" src="/public/leaderboard.js" />
  </Layout>
);
