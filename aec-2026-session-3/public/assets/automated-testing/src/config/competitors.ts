import z from "zod";

const competitorSchema = z.object({
  seat: z.int(),
  name: z.string(),
  ipAddress: z.string(),
});

export type Competitor = z.infer<typeof competitorSchema>;

// Competitors are provided at runtime via the COMPETITORS env var so that the
// list can be updated without rebuilding the image. The value must be a JSON
// array of Competitor objects.
//
// Example (docker-compose.yml):
//   COMPETITORS: |
//     [
//       { "seat": 1, "name": "Alice", "ipAddress": "192.168.1.101" },
//       { "seat": 2, "name": "Bob",   "ipAddress": "192.168.1.102" }
//     ]

export const competitors = process.env.COMPETITORS
  ? z.array(competitorSchema).parse(JSON.parse(process.env.COMPETITORS))
  : [];

if (competitors.length === 0) {
  console.warn(
    "[competitors] No competitors configured. Set the COMPETITORS environment variable.",
  );
}
