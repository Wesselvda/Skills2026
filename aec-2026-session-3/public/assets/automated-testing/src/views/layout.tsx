import type { FC, PropsWithChildren } from "hono/jsx";
import type { JSX } from "hono/jsx/jsx-runtime";
import { Style, css } from "hono/css";

interface Props {
  title: string;
  /** Extra elements to inject into <head> (e.g. <link> tags for fonts/CSS). */
  head?: JSX.Element;
}

const fonts = css`
  @font-face {
    font-family: "JetBrains Mono";
    src: url("/assets/fonts/JetBrainsMono-Regular.woff2") format("woff2");
    font-weight: 400;
    font-style: normal;
  }
  @font-face {
    font-family: "JetBrains Mono";
    src: url("/assets/fonts/JetBrainsMono-Medium.woff2") format("woff2");
    font-weight: 500;
    font-style: normal;
  }
  @font-face {
    font-family: "JetBrains Mono";
    src: url("/assets/fonts/JetBrainsMono-SemiBold.woff2") format("woff2");
    font-weight: 600;
    font-style: normal;
  }
  @font-face {
    font-family: "JetBrains Mono";
    src: url("/assets/fonts/JetBrainsMono-Bold.woff2") format("woff2");
    font-weight: 700;
    font-style: normal;
  }
`;

export const Layout: FC<PropsWithChildren<Props>> = ({
  title,
  children,
  head,
}) => (
  <html lang="en">
    <head>
      <meta charset="UTF-8" />
      <meta name="viewport" content="width=device-width, initial-scale=1.0" />
      <title>{title} · Skill 17</title>
      <Style>{fonts}</Style>
      {head}
    </head>
    <body>{children}</body>
  </html>
);
