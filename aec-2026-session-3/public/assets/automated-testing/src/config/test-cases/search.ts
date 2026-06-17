import { random } from "lodash";
import type { TestCase } from "../../utils/test-runner";
import type { TestData } from "../test-setup";
import { data } from "./data";

const searchTerms = [
  "paul",
  "fantasy",
  "magic",
  "dragon",
  "science",
  "schatz",
  "mystery",
  "thriller",
  "bei",
  "horror",
  "comedy",
];

function randomSearchTerm(): string {
  return searchTerms[random(0, searchTerms.length - 1)];
}

type AstReference = {
  query: string;
  ast: AstNode;
};

const randomAsts = [
  {
    description: "single-term queries",
    generate: (): AstReference => {
      const terms = [randomSearchTerm()];
      return {
        query: terms[0],
        ast: {
          type: "TERM",
          value: terms[0],
        },
      };
    },
  },
  {
    description: "multi-word queries",
    generate: (): AstReference => {
      const terms = [randomSearchTerm(), randomSearchTerm()];
      return {
        query: terms[0] + "%20" + terms[1],
        ast: {
          type: "TERM",
          value: terms[0] + " " + terms[1],
        },
      };
    },
  },
  {
    description: "AND-operator queries",
    generate: (): AstReference => {
      const terms = [randomSearchTerm(), randomSearchTerm()];
      return {
        query: `${terms[0]}%20AND%20${terms[1]}`,
        ast: {
          type: "AND",
          left: {
            type: "TERM",
            value: terms[0],
          },
          right: {
            type: "TERM",
            value: terms[1],
          },
        },
      };
    },
  },
  {
    description: "OR-operator queries",
    generate: (): AstReference => {
      const terms = [randomSearchTerm(), randomSearchTerm()];
      return {
        query: `${terms[0]}%20OR%20${terms[1]}`,
        ast: {
          type: "OR",
          left: {
            type: "TERM",
            value: terms[0],
          },
          right: {
            type: "TERM",
            value: terms[1],
          },
        },
      };
    },
  },
  {
    description: "mixed-operator queries respecting AND precedence",
    generate: (): AstReference => {
      const terms = [
        randomSearchTerm(),
        randomSearchTerm(),
        randomSearchTerm(),
      ];
      return {
        query: `${terms[0]}%20OR%20${terms[1]}%20AND%20${terms[2]}`,
        ast: {
          type: "OR",
          left: {
            type: "TERM",
            value: terms[0],
          },
          right: {
            type: "AND",
            left: {
              type: "TERM",
              value: terms[1],
            },
            right: {
              type: "TERM",
              value: terms[2],
            },
          },
        },
      };
    },
  },
  {
    description: "parentheses-overriding precedence queries",
    generate: (): AstReference => {
      const terms = [
        randomSearchTerm(),
        randomSearchTerm(),
        randomSearchTerm(),
      ];
      return {
        query: `(${terms[0]}%20OR%20${terms[1]})%20AND%20${terms[2]}`,
        ast: {
          type: "AND",
          left: {
            type: "OR",
            left: {
              type: "TERM",
              value: terms[0],
            },
            right: {
              type: "TERM",
              value: terms[1],
            },
          },
          right: {
            type: "TERM",
            value: terms[2],
          },
        },
      };
    },
  },
  {
    description: "nested-parentheses queries",
    generate: (): AstReference => {
      const terms = [
        randomSearchTerm(),
        randomSearchTerm(),
        randomSearchTerm(),
        randomSearchTerm(),
      ];
      return {
        query: `(${terms[0]}%20OR%20(${terms[1]}%20AND%20${terms[2]}))%20AND%20${terms[3]}`,
        ast: {
          type: "AND",
          left: {
            type: "OR",
            left: {
              type: "TERM",
              value: terms[0],
            },
            right: {
              type: "AND",
              left: {
                type: "TERM",
                value: terms[1],
              },
              right: {
                type: "TERM",
                value: terms[2],
              },
            },
          },
          right: {
            type: "TERM",
            value: terms[3],
          },
        },
      };
    },
  },
];

export const search: TestCase<TestData>[] = [
  {
    name: "AST is only returned for debug=true",
    description: "GET /api/books only returns the AST when debug=true",
    points: 0.1,
    parallel: true,
    execute: async ({ api, expect }) => {
      const term = randomSearchTerm();
      const debugValues = [
        { value: "true", shouldHaveAst: true },
        { value: "false", shouldHaveAst: false },
        { value: "", shouldHaveAst: false },
        { value: undefined, shouldHaveAst: false },
      ];
      for (const { value, shouldHaveAst } of debugValues) {
        const url =
          value !== undefined
            ? `/api/books?query=${term}&debug=${value}`
            : `/api/books?query=${term}`;
        const result = await api.get(url);
        expect.toHaveStatus(result, 200);
        const hasAst = "ast" in result.body && result.body.ast;
        if (shouldHaveAst && !hasAst) {
          throw new Error(`debug=${value} should include AST`);
        } else if (!shouldHaveAst && hasAst) {
          throw new Error(
            `debug=${value ?? "undefined"} should not include AST`,
          );
        }
      }
    },
  },
  ...randomAsts.flatMap(({ description, generate }): TestCase<TestData>[] => {
    return [
      {
        name: "Correct AST is returned",
        description: "GET /api/books correctly parses " + description,
        points: 0.2,
        parallel: true,
        execute: async ({ api, expect }) => {
          const { ast, query } = generate();
          const result = await api.get(`/api/books?query=${query}&debug=true`);
          expect.toHaveStatus(result, 200);
          expect.toDeepEqual(
            result.body.ast,
            ast,
            "Query " + query + " was not parsed correctly",
          );
        },
      },
      {
        name: "Books are correctly filtered",
        description:
          "GET /api/books?query=... returns matching books for " + description,
        points: 0.2,
        parallel: true,
        execute: async ({ api, expect }) => {
          const { ast, query } = generate();
          const result = await api.get(`/api/books?query=${query}`);
          expect.toHaveStatus(result, 200);
          let bookIds = [];
          try {
            // @ts-expect-error
            bookIds = result.body.books.map((b) => b.id);
          } catch {
            throw new Error("Response must contain a list of books with ids");
          }
          expect.toDeepEqual(
            bookIds.sort(),
            data.books
              .filter((b) => matchesBook(b, ast))
              .map((b) => b.id)
              .sort(),
            "Query " + query + " did not produce the correct set of books",
          );
        },
      },
    ];
  }),
  {
    name: "Invalid queries return 400",
    description: "GET /api/books with invalid query syntax returns 400 error",
    points: 0.2,
    parallel: true,
    execute: async ({ api, expect }) => {
      const term = randomSearchTerm();
      const invalidQueries = [
        { query: `${term}%20(magic`, reason: "unclosed parenthesis" },
        { query: `${term}%20()`, reason: "empty parentheses" },
        { query: "AND%20OR", reason: "operator without operands" },
        {
          query: `${term})%20OR%20magic`,
          reason: "unmatched closing parenthesis",
        },
        {
          query: `${term}%20AND%20OR%20magic`,
          reason: "consecutive operators",
        },
      ];
      await Promise.all(
        invalidQueries.map(({ query, reason }) =>
          expect.toFailWithStatus(
            () => api.get(`/api/books?query=${query}`),
            400,
            reason,
          ),
        ),
      );
    },
  },
];

interface AstNode {
  type: "TERM" | "AND" | "OR";
  value?: string;
  left?: AstNode;
  right?: AstNode;
}

export function matchesBook(
  book: (typeof data.books)[number],
  node: AstNode,
): boolean {
  switch (node.type) {
    case "TERM": {
      const needle = node.value!.toLowerCase();
      const author = data.authors.find((a) => a.id === book.author_id)!;
      const authorName = `${author.forename} ${author.surname}`.toLowerCase();
      return (
        book.title.toLowerCase().includes(needle) ||
        book.abstract.toLowerCase().includes(needle) ||
        book.category.toLowerCase().includes(needle) ||
        authorName.includes(needle)
      );
    }
    case "AND":
      return matchesBook(book, node.left!) && matchesBook(book, node.right!);
    case "OR":
      return matchesBook(book, node.left!) || matchesBook(book, node.right!);
    default:
      return false;
  }
}
