import { getYear, isSameDay, parse, parseISO } from "date-fns";
import { pickInvalidId, pickValidId } from "../../utils/pick-id";
import type { TestCase } from "../../utils/test-runner";
import type { TestData } from "../test-setup";
import { data } from "./data";
import { checkCover } from "./books";

export const authors: TestCase<TestData>[] = [
  {
    name: "Retrieve all authors",
    description: "GET /api/authors returns 200 with array of authors",
    points: 0.5,
    parallel: true,
    execute: async ({ api, expect }) => {
      const result = await api.get("/api/authors");
      expect.toHaveStatus(result, 200);
      expect.toDeepEqual(
        result.body,
        data.authors.map(({ id, forename, surname }) => ({
          id,
          forename,
          surname,
        })),
        "Must return full list of authors",
      );
    },
  },
  {
    name: "Retrieve author details without localization",
    description: "GET /api/authors/:id returns author",
    points: 0.3,
    parallel: true,
    execute: async ({ api, expect, testData }) => {
      const id = pickValidId(data.authors, testData.seed);
      const result = await api.get(`/api/authors/${id}`);
      expect.toHaveStatus(result, 200);
      const author = getAuthor(id);
      expect.toExtend(
        result.body,
        {
          id: author.id,
          forename: author.forename,
          surname: author.surname,
          date_of_birth: (isoDate: string) => {
            const parsedIso = parseISO(isoDate);
            const reference = parse(
              author.date_of_birth,
              "yyyy-MM-dd",
              new Date(),
            );
            return isSameDay(parsedIso, reference);
          },
          location: {
            value: author.location,
            fallback: false,
          },
          biography: {
            value: author.biography,
            fallback: false,
          },
          original_language: author.original_language,
        },
        "Must return the author details in the original language",
      );
    },
  },
  {
    name: "Retrieve author books without localization",
    description: "GET /api/authors/:id response contains books",
    points: 0.3,
    parallel: true,
    execute: async ({ api, expect, testData }) => {
      const id = pickValidId(data.authors, testData.seed);
      const result = await api.get(`/api/authors/${id}`);
      expect.toHaveStatus(result, 200);
      const author = getAuthor(id);
      expect.toExtend(
        result.body,
        {
          books: author.books.map((book) => ({
            id: book.id,
            title: {
              value: book.title,
              fallback: false,
            },
            year: getYear(new Date(book.released_at)),
          })),
        },
        "Must return the books in the original language",
      );
    },
  },
  {
    name: "Retrieve author books without localization",
    description:
      "GET /api/authors/:id response contains valid links to book covers",
    points: 0.3,
    parallel: true,
    execute: async ({ api, expect, testData }) => {
      const id = pickValidId(data.authors, testData.seed);
      const result = await api.get(`/api/authors/${id}`);
      expect.toHaveStatus(result, 200);
      const author = getAuthor(id);

      expect.toHaveKey(result.body, "books", "Must return the books");
      expect.toNotBeEmpty(result.body.books, "Must return the books");
      await Promise.all(
        author.books.map(async (book, i) => {
          await checkCover(expect, api, result.body.books[i], book.cover_image);
        }),
      );
    },
  },
  {
    name: "Retrieve author with localization",
    description:
      "GET /api/authors/:id response contains the correct language metadata",
    points: 0.1,
    parallel: true,
    execute: async ({ api, expect, testData }) => {
      const id = pickValidId(data.authors, testData.seed);
      for (const locale of data.parsedLocales) {
        const result = await api.get(`/api/authors/${id}`, locale.header);
        expect.toHaveStatus(result, 200);
        const author = getAuthor(id);
        expect.toExtend(
          result.body,
          {
            original_language: author.original_language,
            translated_language:
              author.original_language === locale.expected
                ? null
                : locale.expected,
          },
          "Must return the correct language metadata",
        );
      }
    },
  },
  {
    name: "Retrieve author with localization",
    description:
      "GET /api/authors/:id response is localized with the translation service",
    points: 0.4,
    parallel: true,
    execute: async ({ api, expect, externalApi, testData }) => {
      const id = pickValidId(data.authors, testData.seed);
      for (const locale of data.locales) {
        const result = await api.get(`/api/authors/${id}`, locale);
        expect.toHaveStatus(result, 200);
        const author = getAuthor(id);
        const doNotTranslate = locale === author.original_language;
        const translations = await externalApi.batchTranslateTexts(
          {
            texts: [
              author.location,
              author.biography,
              ...author.books.map((b) => b.title),
            ].map((text) => ({
              text,
              sourceLanguage: author.original_language,
              targetLanguage: locale,
            })),
          },
          doNotTranslate,
        );
        expect.toExtend(
          result.body,
          {
            location: {
              value: translations[0]!.translation ?? author.location,
              fallback: !translations[0]!.success,
            },
            biography: {
              value: translations[1]!.translation ?? author.biography,
              fallback: !translations[1]!.success,
            },
            books: author.books.map((b, i) => ({
              title: {
                value: translations[2 + i]!.translation ?? b.title,
                fallback: !translations[2 + i]!.success,
              },
            })),
          },
          "Must translate the fields",
        );
      }
    },
  },
  {
    name: "Author not found",
    description: "GET /api/authors/:id with invalid id returns 404",
    points: 0.1,
    parallel: true,
    execute: async ({ api, expect, testData }) => {
      await Promise.all(
        [
          pickInvalidId(data.authors, "number", testData.seed),
          "3a",
          pickInvalidId(data.authors, "text", testData.seed),
          pickInvalidId(data.authors, "uuid", testData.seed),
        ].map((invalidId) =>
          expect.toFailWithStatus(
            () => api.get(`/api/authors/${invalidId}`),
            404,
            `non-existent author with id ${invalidId}`,
          ),
        ),
      );
    },
  },
];

function getAuthor(id: number) {
  const author = data.authors.find((a) => a.id === id)!;
  return { ...author, books: data.books.filter((b) => b.author_id === id) };
}
