import type { FC } from "hono/jsx";
import { css, Style } from "hono/css";
import { Layout } from "./layout";

const blinkAnimation = css`
  @keyframes blink {
    0%,
    100% {
      opacity: 1;
    }
    50% {
      opacity: 0.3;
    }
  }
`;

// Catppuccin Mocha palette — competitive-programming / terminal aesthetic
const testerCss = css`
  :-hono-global {
    *,
    *::before,
    *::after {
      box-sizing: border-box;
      margin: 0;
      padding: 0;
    }
    :root {
      --base: #1e1e2e;
      --mantle: #181825;
      --crust: #11111b;
      --surface0: #313244;
      --surface1: #45475a;
      --overlay0: #6c7086;
      --text: #cdd6f4;
      --subtext: #a6adc8;
      --green: #a6e3a1;
      --red: #f38ba8;
      --yellow: #f9e2af;
      --blue: #89b4fa;
      --mauve: #cba6f7;
      --teal: #94e2d5;
      --peach: #fab387;
    }
    body {
      background: var(--base);
      color: var(--text);
      font-family: "JetBrains Mono", "Fira Code", "Cascadia Code", monospace;
      font-size: 14px;
      line-height: 1.6;
      min-height: 100vh;
    }
    .tester-wrap {
      max-width: 900px;
      margin: 0 auto;
      padding: 32px 24px 64px;
    }
    .tester-header {
      display: flex;
      flex-direction: column;
      gap: 16px;
      border-bottom: 1px solid var(--surface0);
      padding-bottom: 20px;
      margin-bottom: 32px;
    }
    .tester-title {
      font-size: 22px;
      font-weight: 700;
      color: var(--blue);
      letter-spacing: -0.5px;
    }
    .tester-subtitle {
      color: var(--overlay0);
      font-size: 12px;
      margin-top: 4px;
    }
    .tester-form {
      display: flex;
      align-items: center;
      gap: 24px;
    }
    .checkbox-label,
    .port-label {
      display: flex;
      align-items: center;
      gap: 8px;
      color: var(--subtext);
      font-size: 13px;
      cursor: pointer;
      user-select: none;
    }
    .tester-checkbox {
      appearance: none;
      width: 18px;
      height: 18px;
      border: 2px solid var(--surface1);
      border-radius: 4px;
      background: var(--mantle);
      cursor: pointer;
      transition: all 0.15s;
      position: relative;
    }
    .tester-checkbox:checked {
      background: var(--blue);
      border-color: var(--blue);
    }
    .tester-checkbox:checked::after {
      content: "✓";
      position: absolute;
      left: 50%;
      top: 50%;
      transform: translate(-50%, -50%);
      color: var(--crust);
      font-size: 12px;
      font-weight: 700;
    }
    .tester-checkbox:hover {
      border-color: var(--overlay0);
    }
    .tester-port {
      width: 80px;
      padding: 6px 10px;
      background: var(--mantle);
      border: 1px solid var(--surface1);
      border-radius: 4px;
      color: var(--text);
      font-family: inherit;
      font-size: 13px;
    }
    .tester-port:focus {
      outline: none;
      border-color: var(--blue);
    }
    .tester-port:disabled {
      color: var(--surface0);
    }
    .run-btn {
      margin-left: auto;
      display: inline-flex;
      align-items: center;
      gap: 8px;
      background: var(--blue);
      color: var(--crust);
      border: none;
      border-radius: 4px;
      padding: 10px 22px;
      font-family: inherit;
      font-size: 13px;
      font-weight: 700;
      letter-spacing: 0.5px;
      cursor: pointer;
      transition:
        background 0.15s,
        transform 0.1s;
      text-transform: uppercase;
    }
    .run-btn:hover:not(:disabled) {
      background: var(--mauve);
    }
    .run-btn:active:not(:disabled) {
      transform: scale(0.97);
    }
    .run-btn:disabled {
      background: var(--surface1);
      color: var(--overlay0);
      cursor: not-allowed;
    }
    .metrics-bar {
      display: flex;
      align-items: center;
      gap: 24px;
      background: var(--mantle);
      border: 1px solid var(--surface0);
      border-radius: 4px;
      padding: 12px 16px;
      margin: 20px 0;
      font-size: 13px;
    }
    .metrics-bar .metric {
      display: flex;
      flex-direction: column;
      gap: 2px;
    }
    .metrics-bar .metric-label {
      color: var(--overlay0);
      font-size: 10px;
      text-transform: uppercase;
      letter-spacing: 1px;
    }
    .metrics-bar .metric-value {
      color: var(--text);
      font-weight: 600;
    }
    .metrics-bar .metric-value.pass {
      color: var(--green);
    }
    .metrics-bar .metric-value.score {
      color: var(--yellow);
    }
    .metrics-divider {
      width: 1px;
      height: 32px;
      background: var(--surface0);
    }
    .error-box {
      background: color-mix(in srgb, var(--red) 10%, var(--mantle));
      border: 1px solid color-mix(in srgb, var(--red) 40%, transparent);
      border-left: 3px solid var(--red);
      border-radius: 4px;
      padding: 12px 16px;
      margin: 20px 0;
      color: var(--red);
      font-size: 13px;
    }
    .test-list {
      list-style: none;
      display: flex;
      flex-direction: column;
      gap: 8px;
    }
    .test-item {
      display: grid;
      grid-template-columns: 28px 1fr auto;
      align-items: start;
      gap: 12px;
      background: var(--mantle);
      border: 1px solid var(--surface0);
      border-radius: 4px;
      padding: 14px 16px;
      transition: border-color 0.2s;
    }
    .test-item:hover {
      border-color: var(--surface1);
    }
    .test-item.passed {
      border-left: 3px solid var(--green);
    }
    .test-item.failed {
      border-left: 3px solid var(--red);
    }
    .test-status-icon {
      display: flex;
      align-items: center;
      justify-content: center;
      width: 20px;
      height: 20px;
      border-radius: 50%;
      font-size: 11px;
      font-weight: 700;
      flex-shrink: 0;
      margin-top: 1px;
    }
    .test-status-icon.pass {
      background: color-mix(in srgb, var(--green) 15%, transparent);
      color: var(--green);
      border: 1px solid color-mix(in srgb, var(--green) 40%, transparent);
    }
    .test-status-icon.fail {
      background: color-mix(in srgb, var(--red) 15%, transparent);
      color: var(--red);
      border: 1px solid color-mix(in srgb, var(--red) 40%, transparent);
    }
    .test-status-icon.pending {
      background: var(--surface0);
      color: var(--overlay0);
      border: 1px solid var(--surface1);
    }
    .test-info {
      min-width: 0;
    }
    .test-name {
      font-weight: 600;
      color: var(--text);
      font-size: 13px;
    }
    .test-desc {
      color: var(--subtext);
      font-size: 12px;
      margin-top: 2px;
      white-space: nowrap;
      overflow: hidden;
      text-overflow: ellipsis;
    }
    .test-result-msg {
      color: var(--red);
      font-size: 11px;
      margin-top: 6px;
      font-style: italic;
    }
    .test-meta {
      display: flex;
      flex-direction: column;
      align-items: flex-end;
      gap: 4px;
      flex-shrink: 0;
    }
    .test-points {
      color: var(--yellow);
      font-size: 12px;
      font-weight: 600;
    }
    .test-duration {
      color: var(--overlay0);
      font-size: 11px;
    }
    .loading-hint {
      color: var(--overlay0);
      font-size: 12px;
      margin-top: 20px;
      text-align: center;
      animation: ${blinkAnimation} 1.2s step-end infinite;
    }
  }
`;

export const TesterPage: FC = () => (
  <Layout title="API Tester" head={<Style>{testerCss}</Style>}>
    <div class="tester-wrap">
      <div id="tester-root" />
    </div>
    <script type="module" src="/public/tester.js" />
  </Layout>
);
