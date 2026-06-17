import type { TestCase } from "../../utils/test-runner";
import type { TestData } from "../test-setup";
import { authors } from "./authors";
import { general } from "./general";
import { books } from "./books";
import { search } from "./search";
import { stock } from "./stock";
import { availability } from "./availability";
import { reviews } from "./reviews";
import { cart } from "./cart";
import { cartItems } from "./cart-items";
import { checkout } from "./checkout";

export const testCases: TestCase<TestData>[] = [
  ...general,
  ...authors,
  ...books,
  ...search,
  ...availability,
  ...stock,
  ...reviews,
  ...cart,
  ...cartItems,
  ...checkout,
];
