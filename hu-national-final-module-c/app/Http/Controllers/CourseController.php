<?php

namespace App\Http\Controllers;

use App\Models\Course;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Http;

class CourseController extends Controller
{
    public function index()
    {
        $courses = Course::with('chapters')->get();
        $courseData = [];

        foreach ($courses as $course) {
            $chapters = [];

            foreach ($course->chapters as $chapter) {
                $chapters[] = [
                    'id' => (int) $chapter->id,
                    'title' => $chapter->title,
                    'credits' => (int) $chapter->credits,
                    'orderIndex' => (int) $chapter->order_index,
                ];
            }

            $courseData[] = [
                'id' => (int) $course->id,
                'slug' => $course->slug,
                'title' => $course->title,
                'description' => $course->description,
                'difficulty' => $course->difficulty,
                'totalChapters' => (int) $course->total_chapters,
                'totalCredits' => (int) $course->total_credits,
                'createdAt' => Carbon::parse($course->created_at)->utc()->format('Y-m-d\TH:i:s.v\Z'),
                'chapters' => $chapters,
            ];
        }

        return response()->json([
            'courses' => $courseData,
        ]);
    }

    public function show(Request $request, $slug)
    {
        $course = Course::with('chapters')->where('slug', $slug)->first();

        if (! $course) {
            return response()->json([
                'error' => 'Course not found',
                'code' => 'COURSE_NOT_FOUND',
            ], 404);
        }

        $token = $request->attributes->get('token');
        $mainBackend = 'http://localhost:5000/api/v1';

        $enrolledResponse = Http::withToken($token)->get($mainBackend.'/users/me/enrolled-courses');
        $courseIds = $enrolledResponse->json('courseIds') ?? [];

        if (! in_array((int) $course->id, $courseIds)) {
            return response()->json([
                'error' => 'Not enrolled in this course',
                'code' => 'NOT_ENROLLED',
            ], 403);
        }

        $completedResponse = Http::withToken($token)->get($mainBackend.'/users/me/completed-chapters');
        $completedChapterIds = $completedResponse->json('chapterIds') ?? [];
        $chapters = [];

        foreach ($course->chapters as $chapter) {
            $chapters[] = [
                'id' => (int) $chapter->id,
                'title' => $chapter->title,
                'description' => $chapter->description,
                'credits' => (int) $chapter->credits,
                'isCompleted' => in_array((int) $chapter->id, $completedChapterIds),
            ];
        }

        return response()->json([
            'course' => [
                'id' => (int) $course->id,
                'slug' => $course->slug,
                'title' => $course->title,
                'description' => $course->description,
                'difficulty' => $course->difficulty,
                'totalChapters' => (int) $course->total_chapters,
                'totalCredits' => (int) $course->total_credits,
                'createdAt' => Carbon::parse($course->created_at)->utc()->format('Y-m-d\TH:i:s.v\Z'),
                'chapters' => $chapters,
            ],
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->all();

        if ($request->has('course')) {
            $data = $request->input('course');
        }

        if (! is_array($data)
            || ! isset($data['title'])
            || ! is_string($data['title'])
            || trim($data['title']) === ''
            || ! isset($data['slug'])
            || ! is_string($data['slug'])
            || $data['slug'] === '') {
            return response()->json([
                'error' => 'Invalid course payload',
                'code' => 'INVALID_COURSE_PAYLOAD',
            ], 400);
        }

        $slugIsValid = true;
        $allowedCharacters = 'abcdefghijklmnopqrstuvwxyz0123456789-';

        foreach (str_split($data['slug']) as $character) {
            if (! str_contains($allowedCharacters, $character)) {
                $slugIsValid = false;
            }
        }

        if (! $slugIsValid) {
            return response()->json([
                'error' => 'Invalid course slug',
                'code' => 'INVALID_COURSE_SLUG',
            ], 400);
        }

        $difficulty = $data['difficulty'] ?? 'beginner';

        if (! in_array($difficulty, ['beginner', 'intermediate', 'advanced'])) {
            return response()->json([
                'error' => 'Invalid difficulty',
                'code' => 'INVALID_DIFFICULTY',
            ], 400);
        }

        if (array_key_exists('description', $data)
            && ! is_string($data['description'])
            && $data['description'] !== null) {
            return response()->json([
                'error' => 'Invalid course payload',
                'code' => 'INVALID_COURSE_PAYLOAD',
            ], 400);
        }

        if (Course::where('slug', $data['slug'])->exists()) {
            return response()->json([
                'error' => 'A course with this slug already exists',
                'code' => 'DUPLICATE_COURSE_SLUG',
            ], 409);
        }

        $course = Course::create([
            'title' => trim($data['title']),
            'slug' => $data['slug'],
            'description' => $data['description'] ?? null,
            'difficulty' => $difficulty,
            'total_chapters' => 0,
            'total_credits' => 0,
        ]);

        $course->refresh();

        return response()->json([
            'course' => [
                'id' => (int) $course->id,
                'slug' => $course->slug,
                'title' => $course->title,
                'description' => $course->description,
                'difficulty' => $course->difficulty,
                'totalChapters' => 0,
                'totalCredits' => 0,
                'createdAt' => Carbon::parse($course->created_at)->utc()->format('Y-m-d\TH:i:s.v\Z'),
                'chapters' => [],
            ],
        ], 201);
    }

    public function update(Request $request, $id)
    {
        if (! ctype_digit((string) $id)) {
            return response()->json([
                'error' => 'Invalid course ID',
                'code' => 'INVALID_ID_FORMAT',
            ], 400);
        }

        $course = Course::find($id);

        if (! $course) {
            return response()->json([
                'error' => 'Course not found',
                'code' => 'COURSE_NOT_FOUND',
            ], 404);
        }

        $data = $request->all();
        $allowedFields = ['title', 'slug', 'description', 'difficulty'];
        $hasAllowedField = false;

        foreach ($allowedFields as $field) {
            if (array_key_exists($field, $data)) {
                $hasAllowedField = true;
            }
        }

        if (! $hasAllowedField) {
            return response()->json([
                'error' => 'Invalid course payload',
                'code' => 'INVALID_COURSE_PAYLOAD',
            ], 400);
        }

        if (array_key_exists('title', $data)
            && (! is_string($data['title']) || trim($data['title']) === '')) {
            return response()->json([
                'error' => 'Invalid course payload',
                'code' => 'INVALID_COURSE_PAYLOAD',
            ], 400);
        }

        if (array_key_exists('description', $data)
            && ! is_string($data['description'])
            && $data['description'] !== null) {
            return response()->json([
                'error' => 'Invalid course payload',
                'code' => 'INVALID_COURSE_PAYLOAD',
            ], 400);
        }

        if (array_key_exists('difficulty', $data)
            && ! in_array($data['difficulty'], ['beginner', 'intermediate', 'advanced'])) {
            return response()->json([
                'error' => 'Invalid difficulty',
                'code' => 'INVALID_DIFFICULTY',
            ], 400);
        }

        if (array_key_exists('slug', $data)) {
            $slugIsValid = is_string($data['slug']) && $data['slug'] !== '';
            $allowedCharacters = 'abcdefghijklmnopqrstuvwxyz0123456789-';

            if ($slugIsValid) {
                foreach (str_split($data['slug']) as $character) {
                    if (! str_contains($allowedCharacters, $character)) {
                        $slugIsValid = false;
                    }
                }
            }

            if (! $slugIsValid) {
                return response()->json([
                    'error' => 'Invalid course slug',
                    'code' => 'INVALID_COURSE_SLUG',
                ], 400);
            }

            $slugExists = Course::where('slug', $data['slug'])
                ->where('id', '!=', $course->id)
                ->exists();

            if ($slugExists) {
                return response()->json([
                    'error' => 'A course with this slug already exists',
                    'code' => 'DUPLICATE_COURSE_SLUG',
                ], 409);
            }
        }

        if (array_key_exists('title', $data)) {
            $course->title = trim($data['title']);
        }

        if (array_key_exists('slug', $data)) {
            $course->slug = $data['slug'];
        }

        if (array_key_exists('description', $data)) {
            $course->description = $data['description'];
        }

        if (array_key_exists('difficulty', $data)) {
            $course->difficulty = $data['difficulty'];
        }

        $course->total_chapters = $course->chapters()->count();
        $course->total_credits = $course->chapters()->sum('credits');
        $course->save();
        $course->load('chapters');

        $chapters = [];

        foreach ($course->chapters as $chapter) {
            $chapters[] = [
                'id' => (int) $chapter->id,
                'title' => $chapter->title,
                'credits' => (int) $chapter->credits,
                'orderIndex' => (int) $chapter->order_index,
            ];
        }

        return response()->json([
            'course' => [
                'id' => (int) $course->id,
                'slug' => $course->slug,
                'title' => $course->title,
                'description' => $course->description,
                'difficulty' => $course->difficulty,
                'totalChapters' => (int) $course->total_chapters,
                'totalCredits' => (int) $course->total_credits,
                'createdAt' => Carbon::parse($course->created_at)->utc()->format('Y-m-d\TH:i:s.v\Z'),
                'chapters' => $chapters,
            ],
        ]);
    }
}
