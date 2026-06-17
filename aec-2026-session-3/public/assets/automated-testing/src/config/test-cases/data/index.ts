import authors from "./authors.json";
import books from "./books.json";
import { hashFile } from "./hash-stream";
import { readdir } from "fs/promises";
import { join } from "path";

const locales = ["en", "de", "hu", "nl"];
const parsedLocales = [
  { header: "", expected: null },
  { header: "*", expected: null },
  ...locales.map((header) => ({ header, expected: header })),
  {
    header: "en;q=0.9",
    expected: "en",
  },
  {
    header: "de,de;q=0.9,en;q=0.8",
    expected: "de",
  },
  {
    header: "fr,en;q=0.8",
    expected: "en",
  },
  {
    header: "fr",
    expected: null,
  },
  {
    header: "en;q=0.5,de;q=0.9",
    expected: "de",
  },
  {
    header: "en,de;q=0.9",
    expected: "en",
  },
];

let hashes: Record<string, string> | undefined = undefined;
export async function getBookCoverHashes(): Promise<Record<string, string>> {
  if (hashes) return hashes;

  hashes = {};
  const coversDir = join(__dirname, "book-covers");
  const files = await readdir(coversDir);

  for (const file of files) {
    const filePath = join(coversDir, file);
    hashes[file] = await hashFile(filePath);
  }

  return hashes;
}
getBookCoverHashes();

export const data = {
  authors,
  books,
  locales,
  parsedLocales,
  getBookCoverHashes,
};
