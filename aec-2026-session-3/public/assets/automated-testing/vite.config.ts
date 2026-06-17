import { defineConfig } from "vite";

export default defineConfig({
  publicDir: false,
  build: {
    outDir: "public",
    emptyOutDir: false,
    rollupOptions: {
      input: {
        tester: "./src/client/tester.tsx",
        leaderboard: "./src/client/leaderboard.tsx",
      },
      output: {
        entryFileNames: "[name].js",
      },
    },
  },
});
