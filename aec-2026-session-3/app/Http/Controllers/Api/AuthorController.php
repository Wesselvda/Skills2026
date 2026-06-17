<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Author;
use App\Services\ExternalApi;
use Illuminate\Http\Request;

class AuthorController extends Controller
{
    public function index()
    {
        $authors = Author::query()
            ->orderBy('id')
            ->get(['id', 'forename', 'surname']);

        return response()->json($authors);
    }

    public function show(Request $request, $id)
    {
        if (! ctype_digit((string) $id)) {
            return response()->json(['message' => 'Author not found'], 404);
        }

        $author = Author::with('books')->find($id);

        if (! $author) {
            return response()->json(['message' => 'Author not found'], 404);
        }

        $requestedLanguage = $this->targetLanguage($request, $author->original_language);
        $translations = $this->authorTranslations($author, $requestedLanguage);
        $location = $translations['location'];
        $biography = $translations['biography'];

        return response()->json([
            'id' => $author->id,
            'forename' => $author->forename,
            'surname' => $author->surname,
            'date_of_birth' => $author->date_of_birth?->toISOString(),
            'location' => $location,
            'biography' => $biography,
            'books' => $author->books->map(fn($book) => [
                'id' => $book->id,
                'title' => $translations['books'][$book->id] ?? [
                    'value' => $book->title,
                    'fallback' => false,
                ],
                'cover' => asset('assets/data/book-covers/' . $book->cover_image),
                'year' => (int) $book->released_at->format('Y'),
            ])->values(),
            'original_language' => $author->original_language,
            'translated_language' => $this->translatedLanguage(
                array_merge([$location, $biography], array_values($translations['books'])),
                $requestedLanguage,
            ),
        ]);
    }

    private function targetLanguage(Request $request, string $originalLanguage)
    {
        $header = $request->header('Accept-Language');
        $supported = ['en', 'de', 'nl', 'hu'];

        if (! $header || $header === '*') {
            return null;
        }

        $language = $this->preferredLanguage($header, $supported);

        if (! $language || $language === $originalLanguage) {
            return null;
        }

        return $language;
    }

    private function localized(string $value, string $sourceLanguage, ?string $targetLanguage)
    {
        if (! $targetLanguage) {
            return [
                'value' => $value,
                'fallback' => false,
            ];
        }

        $translation = app(ExternalApi::class)->translate($value, $sourceLanguage, $targetLanguage);

        if (! $translation) {
            return [
                'value' => $value,
                'fallback' => true,
            ];
        }

        return [
            'value' => $translation,
            'fallback' => false,
        ];
    }

    private function authorTranslations(Author $author, ?string $targetLanguage)
    {
        $result = [
            'location' => [
                'value' => $author->location,
                'fallback' => false,
            ],
            'biography' => [
                'value' => $author->biography,
                'fallback' => false,
            ],
            'books' => [],
        ];

        foreach ($author->books as $book) {
            $result['books'][$book->id] = [
                'value' => $book->title,
                'fallback' => false,
            ];
        }

        if (! $targetLanguage) {
            return $result;
        }

        $requests = [
            [
                'key' => 'location',
                'text' => $author->location,
                'sourceLanguage' => $author->original_language,
                'targetLanguage' => $targetLanguage,
            ],
            [
                'key' => 'biography',
                'text' => $author->biography,
                'sourceLanguage' => $author->original_language,
                'targetLanguage' => $targetLanguage,
            ],
        ];

        foreach ($author->books as $book) {
            if ($author->original_language === $targetLanguage) {
                continue;
            }

            $requests[] = [
                'key' => 'book:' . $book->id,
                'text' => $book->title,
                'sourceLanguage' => $author->original_language,
                'targetLanguage' => $targetLanguage,
            ];
        }

        $responses = app(ExternalApi::class)->translateBatch(array_map(fn($item) => [
            'text' => $item['text'],
            'sourceLanguage' => $item['sourceLanguage'],
            'targetLanguage' => $item['targetLanguage'],
        ], $requests));

        foreach ($requests as $index => $request) {
            $response = $responses[$index] ?? null;
            $translation = $response['translation'] ?? null;
            $fallback = ! (($response['success'] ?? false) && $translation);

            if ($request['key'] === 'location') {
                $result['location'] = [
                    'value' => $fallback ? $author->location : $translation,
                    'fallback' => $fallback,
                ];
            } elseif ($request['key'] === 'biography') {
                $result['biography'] = [
                    'value' => $fallback ? $author->biography : $translation,
                    'fallback' => $fallback,
                ];
            } elseif (str_starts_with($request['key'], 'book:')) {
                $bookId = (int) substr($request['key'], 5);
                $result['books'][$bookId] = [
                    'value' => $fallback ? $result['books'][$bookId]['value'] : $translation,
                    'fallback' => $fallback,
                ];
            }
        }

        return $result;
    }

    private function translatedLanguage(array $fields, ?string $targetLanguage)
    {
        return $targetLanguage;
    }

    private function preferredLanguage(string $header, array $supported)
    {
        $best = null;
        $bestQuality = -1;

        foreach (explode(',', $header) as $part) {
            $pieces = array_map('trim', explode(';', $part));
            $language = strtolower(substr($pieces[0] ?? '', 0, 2));
            $quality = 1.0;

            foreach (array_slice($pieces, 1) as $piece) {
                if (str_starts_with($piece, 'q=')) {
                    $quality = (float) substr($piece, 2);
                }
            }

            if (in_array($language, $supported) && $quality > $bestQuality) {
                $best = $language;
                $bestQuality = $quality;
            }
        }

        return $best;
    }
}
