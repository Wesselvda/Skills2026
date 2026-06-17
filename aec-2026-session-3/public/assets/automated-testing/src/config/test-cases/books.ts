import { getYear } from "date-fns";
import { pickInvalidId, pickValidId } from "../../utils/pick-id";
import type { ExpectApi, TestCase } from "../../utils/test-runner";
import type { TestData } from "../test-setup";
import { data } from "./data";
import { hashFetch } from "./data/hash-stream";
import { FetchClient } from "../../utils/fetch-client";

const formatBook = (b: (typeof data.books)[number]) => {
  const a = data.authors.find((a) => a.id === b.author_id)!;
  return {
    id: b.id,
    title: {
      value: b.title,
      fallback: false,
    },
    author: {
      id: a.id,
      forename: a.forename,
      surname: a.surname,
    },
    year: getYear(new Date(b.released_at)),
  };
};

const recent = [...data.books]
  .sort(
    (a, b) =>
      new Date(b.released_at).getTime() - new Date(a.released_at).getTime(),
  )
  .slice(0, 4);

export const books: TestCase<TestData>[] = [
  // All
  {
    name: "Retrieve all books without localization",
    description: "GET /api/books returns 200 with all books",
    points: 0.3,
    parallel: true,
    execute: async ({ api, expect }) => {
      const result = await api.get("/api/books");
      expect.toHaveStatus(result, 200);
      expect.toExtend(
        result.body.books,
        data.books.map(formatBook),
        "Must return full list of books",
      );
    },
  },
  {
    name: "Retrieve all books without localization",
    description: "GET /api/books?query= returns 200 with all books",
    points: 0.3,
    parallel: true,
    execute: async ({ api, expect }) => {
      const result = await api.get("/api/books?query=");
      expect.toHaveStatus(result, 200);
      expect.toExtend(
        result.body.books,
        data.books.map(formatBook),
        "Must return full list of books",
      );
    },
  },
  {
    name: "Retrieve all books without localization",
    description: "GET /api/books response contains valid links to book covers",
    points: 0.3,
    parallel: true,
    execute: async ({ api, expect }) => {
      const result = await api.get("/api/books");
      expect.toHaveStatus(result, 200);

      expect.toHaveKey(result.body, "books", "Must return the books");
      expect.toNotBeEmpty(result.body.books, "Must return the books");
      await Promise.all(
        data.books.map(async (book, i) => {
          await checkCover(expect, api, result.body.books[i], book.cover_image);
        }),
      );
    },
  },
  {
    name: "Retrieve all books with localization",
    description:
      "GET /api/books response contains the correct language metadata",
    points: 0.1,
    parallel: true,
    execute: async ({ api, expect }) => {
      for (const locale of data.parsedLocales) {
        const result = await api.get("/api/books", locale.header);
        expect.toHaveStatus(result, 200);
        expect.toExtend(
          result.body.books,
          data.books.map((book) => ({
            original_language: book.original_language,
            translated_language:
              book.original_language === locale.expected
                ? null
                : locale.expected,
          })),
          "Must return the correct language metadata",
        );
      }
    },
  },
  {
    name: "Retrieve all books with localization",
    description:
      "GET /api/books response is localized with the translation service",
    points: 0.4,
    parallel: true,
    execute: async ({ api, expect, externalApi }) => {
      for (const locale of data.locales) {
        const result = await api.get("/api/books", locale);
        expect.toHaveStatus(result, 200);
        await Promise.all(
          data.books.map(async (book, i) => {
            const doNotTranslate = locale === book.original_language;
            const translations = await externalApi.batchTranslateTexts(
              {
                texts: [book.title].map((text) => ({
                  text,
                  sourceLanguage: book.original_language,
                  targetLanguage: locale,
                })),
              },
              doNotTranslate,
            );
            expect.toExtend(
              result.body.books[i],
              {
                title: {
                  value: translations[0]!.translation ?? book.title,
                  fallback: !translations[0]!.success,
                },
              },
              "Must translate the fields",
            );
          }),
        );
      }
    },
  },
  // Latest
  {
    name: "Retrieve latest books without localization",
    description: "GET /api/books/latest returns 200 with 4 latest books",
    points: 0.3,
    parallel: true,
    execute: async ({ api, expect }) => {
      const result = await api.get("/api/books/latest");
      expect.toHaveStatus(result, 200);
      expect.toExtend(
        result.body,
        recent.map(formatBook),
        "Must return 4 latest books",
      );
    },
  },
  {
    name: "Retrieve latest books without localization",
    description:
      "GET /api/books/latest response contains valid links to book covers",
    points: 0.3,
    parallel: true,
    execute: async ({ api, expect }) => {
      const result = await api.get("/api/books/latest");
      expect.toHaveStatus(result, 200);
      await Promise.all(
        recent.map(async (book, i) => {
          await checkCover(expect, api, result.body[i], book.cover_image);
        }),
      );
    },
  },
  {
    name: "Retrieve latest books with localization",
    description:
      "GET /api/books/latest response contains the correct language metadata",
    points: 0.1,
    parallel: true,
    execute: async ({ api, expect }) => {
      for (const locale of data.parsedLocales) {
        const result = await api.get("/api/books/latest", locale.header);
        expect.toHaveStatus(result, 200);
        expect.toExtend(
          result.body,
          recent.map((book) => ({
            original_language: book.original_language,
            translated_language:
              book.original_language === locale.expected
                ? null
                : locale.expected,
          })),
          "Must return the correct language metadata",
        );
      }
    },
  },
  {
    name: "Retrieve latest books with localization",
    description:
      "GET /api/books/latest response is localized with the translation service",
    points: 0.4,
    parallel: true,
    execute: async ({ api, expect, externalApi }) => {
      for (const locale of data.locales) {
        const result = await api.get("/api/books/latest", locale);
        expect.toHaveStatus(result, 200);
        await Promise.all(
          recent.map(async (book, i) => {
            const doNotTranslate = locale === book.original_language;
            const translations = await externalApi.batchTranslateTexts(
              {
                texts: [book.title].map((text) => ({
                  text,
                  sourceLanguage: book.original_language,
                  targetLanguage: locale,
                })),
              },
              doNotTranslate,
            );
            expect.toExtend(
              result.body[i],
              {
                title: {
                  value: translations[0]!.translation ?? book.title,
                  fallback: !translations[0]!.success,
                },
              },
              "Must translate the fields",
            );
          }),
        );
      }
    },
  },
  // By id
  {
    name: "Retrieve book details without localization",
    description: "GET /api/books/:id returns book",
    points: 0.3,
    parallel: true,
    execute: async ({ api, expect, testData }) => {
      const id = pickValidId(data.books, testData.seed);
      const result = await api.get(`/api/books/${id}`);
      expect.toHaveStatus(result, 200);
      const book = getBook(id);
      expect.toExtend(
        result.body,
        {
          id: book.id,
          title: {
            value: book.title,
            fallback: false,
          },
          author: {
            id: book.author.id,
            forename: book.author.forename,
            surname: book.author.surname,
          },
          year: getYear(new Date(book.released_at)),
          price: book.price,
          category: {
            value: book.category,
            fallback: false,
          },
          abstract: {
            value: book.abstract,
            fallback: false,
          },
        },
        "Must return the author details in the original language",
      );
    },
  },
  {
    name: "Retrieve book without localization",
    description:
      "GET /api/authors/:id response contains valid links to book covers",
    points: 0.3,
    parallel: true,
    execute: async ({ api, expect, testData }) => {
      const id = pickValidId(data.books, testData.seed);
      const result = await api.get(`/api/books/${id}`);
      expect.toHaveStatus(result, 200);
      const book = getBook(id);
      await checkCover(expect, api, result.body, book.cover_image);
    },
  },
  {
    name: "Retrieve book without localization",
    description: "GET /api/authors/:id response contains reviews",
    points: 0.3,
    parallel: true,
    execute: async ({ api, expect, testData }) => {
      const id = pickValidId(data.books, testData.seed);

      try {
        const result = await api.post(`/api/books/${id}/review`, {
          rating: 4,
          text: "Good",
        });
      } catch {
        throw new Error("Unable to create a review");
      }

      const result = await api.get(`/api/books/${id}`);
      expect.toHaveStatus(result, 200);

      expect.toHaveKey(result.body, "reviews", "Must have reviews");
      expect.toBeArray(result.body.reviews, "Reviews must be an array");

      for (const review of result.body.reviews) {
        expect.toHaveKey(review, "rating", "Each review must have a rating");
        expect.toHaveKey(review, "text", "Each review must have text");
      }

      const avgRating =
        result.body.reviews.reduce(
          (sum: number, r: { rating: number }) => sum + r.rating,
          0,
        ) / result.body.reviews.length;
      expect.toEqual(
        Math.round(result.body.rating * 100),
        Math.round(avgRating * 100),
        "Rating must be the average",
      );
    },
  },
  {
    name: "Retrieve book with localization",
    description:
      "GET /api/books/:id response contains the correct language metadata",
    points: 0.1,
    parallel: true,
    execute: async ({ api, expect, testData }) => {
      const id = pickValidId(data.books, testData.seed);
      for (const locale of data.parsedLocales) {
        const result = await api.get(`/api/books/${id}`, locale.header);
        expect.toHaveStatus(result, 200);
        const book = getBook(id);
        expect.toExtend(
          result.body,
          {
            original_language: book.original_language,
            translated_language:
              book.original_language === locale.expected
                ? null
                : locale.expected,
          },
          "Must return the correct language metadata",
        );
      }
    },
  },
  {
    name: "Retrieve book with localization",
    description:
      "GET /api/books/:id response is localized with the translation service",
    points: 0.4,
    parallel: true,
    execute: async ({ api, expect, externalApi, testData }) => {
      const id = pickValidId(data.books, testData.seed);
      for (const locale of data.locales) {
        const result = await api.get(`/api/books/${id}`, locale);
        expect.toHaveStatus(result, 200);
        const book = getBook(id);
        const doNotTranslate = locale === book.original_language;
        const translations = await externalApi.batchTranslateTexts(
          {
            texts: [book.title, book.category, book.abstract].map((text) => ({
              text,
              sourceLanguage: book.original_language,
              targetLanguage: locale,
            })),
          },
          doNotTranslate,
        );
        expect.toExtend(
          result.body,
          {
            title: {
              value: translations[0]!.translation ?? book.title,
              fallback: !translations[0]!.success,
            },
            category: {
              value: translations[1]!.translation ?? book.category,
              fallback: !translations[1]!.success,
            },
            abstract: {
              value: translations[2]!.translation ?? book.abstract,
              fallback: !translations[2]!.success,
            },
          },
          "Must translate the fields",
        );
      }
    },
  },
  {
    name: "Book not found",
    description: "GET /api/books/:id with invalid id returns 404",
    points: 0.1,
    parallel: true,
    execute: async ({ api, expect, testData }) => {
      for (const invalidId of [
        pickInvalidId(data.books, "number", testData.seed),
        "3a",
        pickInvalidId(data.books, "text", testData.seed),
        pickInvalidId(data.books, "uuid", testData.seed),
      ]) {
        await expect.toFailWithStatus(
          () => api.get(`/api/books/${invalidId}`),
          404,
          `non-existent books with id ${invalidId}`,
        );
      }
    },
  },
];

function getBook(id: number) {
  const book = data.books.find((b) => b.id === id)!;
  return {
    ...book,
    author: data.authors.find((a) => a.id === book.author_id)!,
  };
}

export async function checkCover(
  expect: ExpectApi,
  api: FetchClient,
  book: unknown,
  correctImageFile: string,
) {
  expect.toHaveKey(book, "cover", "Books must have a cover URL");
  // @ts-expect-error Cover has been checked
  const cover = book.cover;
  const url = cover.startsWith("/") ? api.baseUrl + cover : cover;
  const hash = await hashFetch(url);
  const referenceHash = (await data.getBookCoverHashes())![correctImageFile];
  expect.toEqual(
    referenceHash,
    hash,
    `Cover URL must return the correct file (${url})`,
  );
}
