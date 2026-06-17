import { createHash } from "crypto";
import { createReadStream } from "fs";

export async function hashStream(
  stream: NodeJS.ReadableStream,
): Promise<string> {
  const hash = createHash("sha256");

  for await (const chunk of stream) {
    hash.update(chunk);
  }

  return hash.digest("hex");
}

export async function hashFile(filePath: string): Promise<string> {
  const stream = createReadStream(filePath);
  return hashStream(stream);
}

export async function hashFetch(url: string): Promise<string> {
  try {
    const res = await fetch(url);
    if (!res.body) {
      throw new Error(`No response body from ${url}`);
    }

    const hash = createHash("sha256");

    for await (const chunk of res.body) {
      hash.update(chunk);
    }

    return hash.digest("hex");
  } catch (e) {
    throw new Error("Unable to load the cover at " + url + ` (${e.message})`);
  }
}
