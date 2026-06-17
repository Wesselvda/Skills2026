import { randomUUID } from "crypto";
import { dRandom } from "./deterministic-random";

export type InvalidIdType = "number" | "text" | "uuid";

export function pickValidId<T extends { id: number }>(
  collection: T[],
  seed: string,
): T["id"] {
  if (collection.length === 0) {
    throw new Error("Cannot pick valid id from empty collection");
  }
  const index = dRandom(seed, 0, collection.length - 1);
  return collection[index]!.id;
}

export function pickInvalidId<T extends { id: number }>(
  collection: T[],
  type: InvalidIdType = "number",
  seed: string,
): unknown {
  switch (type) {
    case "number": {
      const maxId = Math.max(0, ...collection.map((c) => c.id));
      return dRandom(seed, maxId + 1, 2 * maxId + 5);
    }
    case "text": {
      const invalidTextIds = ["abc", "invalid", "not-an-id", "b4a"];
      const index = dRandom(seed, 0, invalidTextIds.length - 1);
      return invalidTextIds[index]!;
    }
    case "uuid": {
      return randomUUID();
    }
    default:
      throw new Error(`Invalid id type: ${type}`);
  }
}
