import { pickInvalidId } from "../../utils/pick-id";
import type { TestCase } from "../../utils/test-runner";
import type { TestData } from "../test-setup";
import { data } from "./data";
import { dRandom } from "../../utils/deterministic-random";

export const reviews: TestCase<TestData>[] = [
  {
    name: "Valid review creation",
    description: "POST /api/books/1/review creates review with rating and text",
    points: 0.2,
    execute: async ({ api, expect, externalApi, testData }) => {
      const rating = dRandom(testData.seed + "asdr", 1, 5);
      const result = await api.post("/api/books/1/review", {
        rating,
        text: lipsum,
      });
      expect.toHaveStatus(result, 200);
      expect.toHaveKey(result.body, "id", "Review must have an id");
      expect.toEqual(result.body.book_id, 1, "Review must have the book id");
      expect.toEqual(
        result.body.rating,
        rating,
        "Review must have the correct rating",
      );
      const words = (await externalApi.checkContent(lipsum)).flaggedWords;
      const correctedText = [...lipsum];
      for (const word of words) {
        for (let i = word.start; i < correctedText.length; i++) {
          if (correctedText[i].match(/[a-zA-Z]/)) {
            correctedText[i] = "*";
          } else {
            break;
          }
        }
      }
      expect.toEqual(
        result.body.text,
        correctedText.join(""),
        "Review must have a moderated text",
      );
    },
  },
  {
    name: "Valid review creation",
    description: "POST /api/books/1/review stores the review in the database",
    points: 0.2,
    execute: async ({ api, expect, testData }) => {
      let before;
      try {
        before = await api.get("/api/books/1");
      } catch (e) {
        throw new Error("Unable to load the reviews using GET /api/books/1");
      }

      const rating = dRandom(testData.seed + "asdfii", 1, 5);
      const result = await api.post("/api/books/1/review", {
        rating,
        text: lipsum.substring(20, 60),
      });
      expect.toHaveStatus(result, 200);

      let diff = 0;
      let ratingChanged = false;
      let addedReview = true;
      try {
        const after = await api.get("/api/books/1");

        diff = after.body.reviews.length - before.body.reviews.length;
        ratingChanged =
          rating === before.body.rating ||
          before.body.rating !== after.body.rating;
        addedReview = after.body.reviews.some(
          (r: { rating: number; text: string }) =>
            r.rating === result.body.rating && r.text === result.body.text,
        );
      } catch (e) {
        throw new Error("Unable to load the reviews using GET /api/books/1");
      }

      expect.toEqual(diff, 1, "No review created");
      expect.toEqual(ratingChanged, true, "The rating did not change");
      expect.toEqual(addedReview, true, "The rating did not change");
    },
  },
  {
    name: "Invalid review creation",
    description: "POST /api/books/1/review with invalid data returns 422",
    points: 0.2,
    execute: async ({ api, expect }) => {
      await Promise.all(
        invalidReviews.map((review) =>
          expect.toFailWithStatus(
            () => api.post("/api/books/1/review", review.data),
            422,
            review.message,
          ),
        ),
      );
    },
  },
  {
    name: "Review non-existing book",
    description:
      "POST /api/books/:id/review returns 404 for non-existing books",
    points: 0.1,
    execute: async ({ api, expect, testData }) => {
      await Promise.all(
        [
          pickInvalidId(data.books, "number", testData.seed),
          "3a",
          pickInvalidId(data.books, "text", testData.seed),
          pickInvalidId(data.books, "uuid", testData.seed),
        ].map((invalidId) =>
          expect.toFailWithStatus(
            () =>
              api.post(`/api/authors/${invalidId}/review`, {
                rating: 4,
                text: "Good",
              }),
            404,
            `non-existent book with id ${invalidId}`,
          ),
        ),
      );
    },
  },
];

const invalidReviews = [
  {
    data: {
      rating: 0,
      text: "Terrible",
    },
    message: "rating below minimum",
  },
  {
    data: {
      rating: 6,
      text: "Beyond perfect",
    },
    message: "rating above minimum",
  },
  {
    data: {
      text: "Text",
    },
    message: "missing rating",
  },
  {
    data: {
      rating: 5,
      text: "",
    },
    message: "empty text field",
  },
  {
    data: {
      rating: 5,
    },
    message: "missing text",
  },
];

const lipsum =
  "Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat, sed diam voluptua. At vero eos et accusam et justo duo dolores et ea rebum. Stet clita kasd gubergren, no sea takimata sanctus est Lorem ipsum dolor sit amet. Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat, sed diam voluptua. At vero eos et accusam et justo duo dolores et ea rebum. Stet clita kasd gubergren, no sea takimata sanctus est Lorem ipsum dolor sit amet. Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat, sed diam voluptua. At vero eos et accusam et justo duo dolores et ea rebum. Stet clita kasd gubergren, no sea takimata sanctus est Lorem ipsum dolor sit amet.";
