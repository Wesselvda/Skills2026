<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Book;
use App\Models\Review;
use App\Services\BookQueryParser;
use App\Services\ExternalApi;
use Illuminate\Http\Request;
use InvalidArgumentException;

class BookController extends Controller
{
    public function index(Request $request)
    {
        $books = Book::with('author')->orderBy('id')->get();
        $targetLanguage = $this->targetLanguage($request, null);
        $ast = null;

        if ($request->filled('query')) {
            try {
                $ast = app(BookQueryParser::class)->parse($request->query('query'));
            } catch (InvalidArgumentException) {
                return response()->json(['message' => 'Invalid query'], 400);
            }

            $books = $books->filter(fn(Book $book) => $this->matchesBook($book, $ast))->values();
        }

        $response = [
            'books' => $this->bookSummaries($books, $targetLanguage),
        ];

        if ($request->query('debug') === 'true' && $ast) {
            $response['ast'] = $ast;
        }

        return response()->json($response);
    }

    public function latest(Request $request)
    {
        $books = Book::with('author')
            ->orderByDesc('released_at')
            ->limit(4)
            ->get();
        $targetLanguage = $this->targetLanguage($request, null);

        return response()->json(
            $this->bookSummaries($books, $targetLanguage),
        );
    }

    public function show(Request $request, $id)
    {
        if (! ctype_digit((string) $id)) {
            return response()->json(['message' => 'Book not found'], 404);
        }

        $book = Book::with(['author', 'reviews'])->find($id);

        if (! $book) {
            return response()->json(['message' => 'Book not found'], 404);
        }

        $targetLanguage = $this->targetLanguage($request, $book->original_language);

        $localized = $this->localizedBatch([
            ['key' => 'title', 'value' => $book->title, 'source' => $book->original_language],
            ['key' => 'category', 'value' => $book->category, 'source' => $book->original_language],
            ['key' => 'abstract', 'value' => $book->abstract, 'source' => $book->original_language],
        ], $targetLanguage);

        return response()->json([
            ...$this->bookSummary($book, $targetLanguage, $localized['title'] ?? null),
            'price' => (float) $book->price,
            'rating' => (float) $book->reviews->avg('rating'),
            'reviews' => $book->reviews->map(fn(Review $review) => [
                'rating' => $review->rating,
                'text' => $review->text,
            ])->values(),
            'category' => $localized['category'],
            'abstract' => $localized['abstract'],
            'stock' => $this->availableStock($book),
        ]);
    }

    public function updateStock(Request $request, $id)
    {
        if (! ctype_digit((string) $id)) {
            return response()->json(['message' => 'Book not found'], 404);
        }

        $book = Book::find($id);

        if (! $book) {
            return response()->json(['message' => 'Book not found'], 404);
        }

        $data = $request->validate([
            'stock' => ['required', 'integer', 'min:0'],
        ]);

        if ($data['stock'] < $book->stock && $data['stock'] < $this->reservedCount($book->id)) {
            return response()->json(['message' => 'Validation error'], 422);
        }

        $book->update(['stock' => $data['stock']]);

        return response()->json([
            'id' => $book->id,
            'total_stock' => $book->stock,
            'stock' => $this->availableStock($book),
        ]);
    }

    public function availability($id)
    {
        if (! ctype_digit((string) $id)) {
            return response()->json(['message' => 'Book not found'], 404);
        }

        if (! Book::whereKey($id)->exists()) {
            return response()->json(['message' => 'Book not found'], 404);
        }

        return response()->stream(function () use ($id) {
            $externalApi = app(ExternalApi::class);
            $stores = $externalApi->storesForBook($id);

            $stores = array_map(fn($store) => [
                'store_id' => $store['storeId'],
                'name' => $store['name'],
            ], $stores);

            echo "event: stores\n";
            echo 'data: ' . json_encode($stores) . "\n\n";
            flush();

            foreach ($stores as $store) {
                $storeId = $store['store_id'] ?? null;

                if (! $storeId) {
                    continue;
                }

                $availability = $externalApi->storeAvailability($storeId, $id);

                if (! $availability) {
                    continue;
                }

                $availability = [
                    'store_id' => $availability['storeId'],
                    'availability' => $availability['availability'],
                    'inventory' => $availability['inventory'] ?? null,
                ];

                echo "event: availability\n";
                echo 'data: ' . json_encode($availability) . "\n\n";
                flush();
            }
        }, 200, [
            'Content-Type' => 'text/event-stream',
            'Cache-Control' => 'no-cache',
            'X-Accel-Buffering' => 'no',
        ]);
    }

    public function storeReview(Request $request, $id)
    {
        if (! ctype_digit((string) $id)) {
            return response()->json(['message' => 'Book not found'], 404);
        }

        $book = Book::find($id);

        if (! $book) {
            return response()->json(['message' => 'Book not found'], 404);
        }

        $data = $request->validate([
            'rating' => ['required', 'integer', 'between:1,5'],
            'text' => ['required', 'string'],
        ]);

        $flaggedWords = app(ExternalApi::class)->moderate($data['text']);
        $text = $this->maskSensitiveWords($data['text'], $flaggedWords);

        $review = $book->reviews()->create([
            'rating' => $data['rating'],
            'text' => $text,
            'flagged' => count($flaggedWords) > 0,
        ]);

        return response()->json([
            'id' => $review->id,
            'book_id' => $review->book_id,
            'rating' => $review->rating,
            'text' => $review->text,
        ]);
    }

    private function bookSummaries($books, ?string $targetLanguage = null)
    {
        $fields = [];

        foreach ($books as $book) {
            $fields[] = [
                'key' => $book->id,
                'value' => $book->title,
                'source' => $book->original_language,
            ];
        }

        $localized = $this->localizedBatch($fields, $targetLanguage);

        return $books->map(fn(Book $book) => $this->bookSummary(
            $book,
            $targetLanguage,
            $localized[$book->id] ?? null,
        ))->values();
    }

    private function bookSummary(Book $book, ?string $targetLanguage = null, ?array $title = null)
    {
        $language = $targetLanguage;

        if ($language === $book->original_language) {
            $language = null;
        }

        return [
            'id' => $book->id,
            'title' => $title ?? $this->localized($book->title, $book->original_language, $language),
            'cover' => asset('assets/data/book-covers/' . $book->cover_image),
            'author' => [
                'id' => $book->author->id,
                'forename' => $book->author->forename,
                'surname' => $book->author->surname,
            ],
            'year' => (int) $book->released_at->format('Y'),
            'original_language' => $book->original_language,
            'translated_language' => $language,
        ];
    }

    private function targetLanguage(Request $request, ?string $originalLanguage)
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

    private function localizedBatch(array $fields, ?string $targetLanguage)
    {
        $result = [];

        foreach ($fields as $field) {
            $result[$field['key']] = [
                'value' => $field['value'],
                'fallback' => false,
            ];
        }

        if (! $targetLanguage) {
            return $result;
        }

        $requests = [];
        $keys = [];

        foreach ($fields as $field) {
            if ($field['source'] === $targetLanguage) {
                continue;
            }

            $keys[] = $field['key'];
            $requests[] = [
                'text' => $field['value'],
                'sourceLanguage' => $field['source'],
                'targetLanguage' => $targetLanguage,
            ];
        }

        $responses = app(ExternalApi::class)->translateBatch($requests);

        foreach ($keys as $index => $key) {
            $response = $responses[$index] ?? null;
            $translation = $response['translation'] ?? null;
            $success = ($response['success'] ?? false) && $translation;

            $result[$key] = [
                'value' => $success ? $translation : $result[$key]['value'],
                'fallback' => ! $success,
            ];
        }

        return $result;
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

    private function reservedCount(int $bookId)
    {
        return \App\Models\CartItem::query()
            ->where('book_id', $bookId)
            ->whereHas('cart', fn($query) => $query->where('expires_at', '>', now()))
            ->sum('quantity');
    }

    private function availableStock(Book $book)
    {
        return max(0, $book->stock - $this->reservedCount($book->id));
    }

    private function matchesBook(Book $book, array $node)
    {
        if ($node['type'] === 'AND') {
            return $this->matchesBook($book, $node['left']) && $this->matchesBook($book, $node['right']);
        }

        if ($node['type'] === 'OR') {
            return $this->matchesBook($book, $node['left']) || $this->matchesBook($book, $node['right']);
        }

        $needle = strtolower($node['value']);
        $fields = [
            $book->title,
            $book->abstract,
            $book->category,
            $book->author->forename . ' ' . $book->author->surname,
        ];

        foreach ($fields as $field) {
            if (str_contains(strtolower($field), $needle)) {
                return true;
            }
        }

        return false;
    }

    private function maskSensitiveWords(string $text, array $flaggedWords)
    {
        $starts = [];

        foreach ($flaggedWords as $word) {
            if (isset($word['start'])) {
                $starts[] = (int) $word['start'];
            }
        }

        rsort($starts);

        foreach ($starts as $start) {
            $end = $start;

            while ($end < strlen($text) && ctype_alpha($text[$end])) {
                $end++;
            }

            if ($end > $start) {
                $text = substr($text, 0, $start) . str_repeat('*', $end - $start) . substr($text, $end);
            }
        }

        return $text;
    }
}
