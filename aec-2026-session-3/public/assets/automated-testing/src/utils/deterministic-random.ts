import crypto from "crypto";

export function dRandom(seed: string, min: number, max: number) {
  const hash = crypto.createHash("md5").update(seed).digest("hex");
  const number = parseInt(hash.substring(0, 8), 16) & 0x7fffffff;
  const rand = (number % 100) / 100.0;
  return Math.round(min + (max - min) * rand);
}
