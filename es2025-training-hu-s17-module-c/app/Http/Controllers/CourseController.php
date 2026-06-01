<?php

namespace App\Http\Controllers;

use App\Models\Enrollment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class CourseController extends Controller
{
    public function index(Request $request)
    {
        // Logic to retrieve and return all courses

        // Fetch courses from api

        $response = Http::get('http://localhost:5000/api/courses', [
            'headers' => [
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ]
        ]);

        if ($response->successful()) {
            $enrolledCourses = Enrollment::where('user_id', $request->user()->id);

            $mappedCourses = collect($response->json()['courses'])->map(function ($course) use ($enrolledCourses) {
                return [
                    'id' => $course['id'],
                    'title' => $course['title'],
                    'description' => $course['description'],
                    'difficulty' => $course['difficulty'],
                    'totalChapters' => $course['totalChapters'],
                    'totalCredits' => $course['totalCredits'],
                    'isEnrolled' => $enrolledCourses->where('course_id', $course['id'])->exists(),
                ];
            });

            return response()->json(['courses' => $mappedCourses]);
        } else {
            return response()->json(['message' => 'Service Unavailable'], 503);
        }
    }

    public function show(int $id)
    {
        $response = Http::get("http://localhost:5000/api/courses/{$id}", [
            'headers' => [
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ]
        ]);

        if ($response->successful()) {
            $data = $response->json()['course'];

            $user = request()->user();

            return response()->json(['course' => [
                'id' => $data['id'],
                'title' => $data['title'],
                'description' => $data['description'],
                'difficulty' => $data['difficulty'],
                'totalChapters' => $data['totalChapters'],
                'totalCredits' => $data['totalCredits'],
                'isEnrolled' => $user->enrollments()->where('course_id', $data['id'])->exists(),
                'chapters' => collect($data['chapters'])->map(function ($chapter) use ($user) {
                    return [
                        'id' => $chapter['id'],
                        'title' => $chapter['title'],
                        'description' => $chapter['description'],
                        'credits' => $chapter['credits'],
                        'isCompleted' => $user->chapterCompletions()->where('chapter_id', $chapter['id'])->exists(),
                    ];
                }),
            ]]);
        } else {
            return response()->json(['message' => 'Course not found'], 404);
        }
    }

    public function enroll(int $id)
    {
        $response = Http::get("http://localhost:5000/api/courses/{$id}", [
            'headers' => [
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ]
        ]);

        if ($response->successful()) {
            $data = $response->json()['course'];

            $user = request()->user();

            if ($user->enrollments()->where('course_id', $data['id'])->exists()) {
                return response()->json(['message' => 'Already enrolled in this course'], 409);
            }

            Enrollment::create([
                'user_id' => $user->id,
                'course_id' => $data['id'],
            ]);

            return response()->json(['message' => 'Successfully enrolled in course'], 200);
        } else {
            return response()->json(['message' => 'Course not found'], 404);
        }
    }

    public function complete(int $id, int $chapterId)
    {
        $response = Http::get("http://localhost:5000/api/courses/{$id}", [
            'headers' => [
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ]
        ]);

        if ($response->successful()) {
            $data = $response->json()['course'];

            // Check if chapter exists
            $chapter = collect($data['chapters'])->firstWhere('id', $chapterId);

            $user = request()->user();

            if (!$user->enrollments()->where('course_id', $id)->exists()) {
                return response()->json(['message' => 'Not enrolled in this course'], 403);
            }

            if (!$chapter) {
                return response()->json(['message' => 'Chapter not found'], 404);
            }

            if ($user->chapterCompletions()->where('chapter_id', $chapter['id'])->exists()) {
                return response()->json(['message' => 'Chapter already completed'], 409);
            }

            $user->chapterCompletions()->create([
                'chapter_id' => $chapter['id'],
                'credits_earned' => $chapter['credits'],
            ]);

            $user->credit_balance += $chapter['credits'];
            $user->save();

            return response()->json([
                'message' => 'Chapter completed',
                'creditsEarned' => $chapter['credits'],
                'newBalance' => (int) $user->credit_balance,
            ], 200);
        } else {
            return response()->json(['message' => 'Chapter not found'], 404);
        }
    }
}
