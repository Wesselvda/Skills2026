<?php

namespace App\Http\Controllers;

use App\Models\Chapter;
use App\Models\Course;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class QuizController extends Controller
{
    public function validateAnswers(Request $request, $slug, $chapterId)
    {
        $answers = $request->input('answers');

        if (! is_array($answers)) {
            return response()->json([
                'error' => 'Invalid quiz payload',
                'code' => 'INVALID_QUIZ_PAYLOAD',
            ], 400);
        }

        $course = Course::where('slug', $slug)->first();

        if (! $course) {
            return response()->json([
                'error' => 'Course not found',
                'code' => 'COURSE_NOT_FOUND',
            ], 404);
        }

        $chapter = Chapter::with('quizQuestions.options')
            ->where('course_id', $course->id)
            ->where('id', $chapterId)
            ->first();

        if (! $chapter) {
            return response()->json([
                'error' => 'Chapter not found',
                'code' => 'CHAPTER_NOT_FOUND',
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

        if ((int) $chapter->order_index > 1) {
            $previousChapter = Chapter::where('course_id', $course->id)
                ->where('order_index', '<', $chapter->order_index)
                ->orderByDesc('order_index')
                ->first();

            $completedResponse = Http::withToken($token)->get($mainBackend.'/users/me/completed-chapters');
            $completedChapterIds = $completedResponse->json('chapterIds') ?? [];

            if ($previousChapter && ! in_array((int) $previousChapter->id, $completedChapterIds)) {
                return response()->json([
                    'error' => 'Previous chapter must be completed first',
                    'code' => 'CHAPTER_LOCKED',
                ], 403);
            }
        }

        $passed = true;

        foreach ($chapter->quizQuestions as $question) {
            $selectedOptionId = null;

            foreach ($answers as $answer) {
                if (isset($answer['questionId']) && (int) $answer['questionId'] === (int) $question->id) {
                    $selectedOptionId = $answer['selectedOptionId'] ?? null;
                }
            }

            $correctOptionId = null;

            foreach ($question->options as $option) {
                if ((int) $option->is_correct === 1) {
                    $correctOptionId = $option->option_id;
                }
            }

            if ($selectedOptionId !== $correctOptionId) {
                $passed = false;
            }
        }

        if (! $passed) {
            return response()->json([
                'passed' => false,
            ]);
        }

        try {
            $completionResponse = Http::withToken($token)
                ->post($mainBackend.'/courses/'.$course->id.'/chapters/'.$chapter->id.'/complete');
        } catch (ConnectionException $exception) {
            return response()->json([
                'passed' => true,
                'error' => 'Chapter completion failed',
                'code' => 'CHAPTER_COMPLETION_FAILED',
            ], 503);
        }

        if ($completionResponse->failed()) {
            return response()->json([
                'passed' => true,
                'error' => 'Chapter completion failed',
                'code' => 'CHAPTER_COMPLETION_FAILED',
                'mainBackendStatus' => $completionResponse->status(),
                'mainBackendBody' => $completionResponse->json() ?? $completionResponse->body(),
            ], 502);
        }

        return response()->json([
            'passed' => true,
        ]);
    }
}
