<?php

namespace App\Http\Controllers;

use App\Models\Chapter;
use App\Models\Course;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class ChapterController extends Controller
{
    public function show(Request $request, $slug, $chapterId)
    {
        $course = Course::where('slug', $slug)->first();

        if (! $course) {
            return response()->json([
                'error' => 'Course not found',
                'code' => 'COURSE_NOT_FOUND',
            ], 404);
        }

        $chapter = Chapter::with(['contentBlocks.chunks', 'quizQuestions.options'])
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

        $content = [];

        foreach ($chapter->contentBlocks as $block) {
            if (in_array($block->type, ['h1', 'h2', 'h3', 'h4'])) {
                $content[] = [
                    'type' => $block->type,
                    'orderIndex' => (int) $block->order_index,
                    'text' => $block->text,
                ];
            }

            if ($block->type === 'paragraph' || $block->type === 'list_item') {
                $html = '';

                foreach ($block->chunks as $chunk) {
                    $text = e($chunk->text);

                    if ($chunk->italic) {
                        $text = '<em>'.$text.'</em>';
                    }

                    if ($chunk->bold) {
                        $text = '<strong>'.$text.'</strong>';
                    }

                    $html .= $text;
                }

                if ($block->type === 'paragraph') {
                    $html = '<p>'.$html.'</p>';
                } else {
                    $html = '<li>'.$html.'</li>';
                }

                $content[] = [
                    'type' => $block->type,
                    'orderIndex' => (int) $block->order_index,
                    'html' => $html,
                    'rawText' => $block->raw_text,
                ];
            }

            if ($block->type === 'image') {
                $content[] = [
                    'type' => 'image',
                    'orderIndex' => (int) $block->order_index,
                    'url' => $block->url,
                    'alt' => $block->img_alt,
                ];
            }

            if ($block->type === 'video' || $block->type === 'link') {
                $content[] = [
                    'type' => $block->type,
                    'orderIndex' => (int) $block->order_index,
                    'url' => $block->url,
                    'title' => $block->text,
                ];
            }
        }

        $questions = [];

        foreach ($chapter->quizQuestions as $question) {
            $options = [];

            foreach ($question->options as $option) {
                $options[] = [
                    'id' => $option->option_id,
                    'text' => $option->text,
                ];
            }

            $questions[] = [
                'id' => (int) $question->id,
                'text' => $question->text,
                'options' => $options,
            ];
        }

        return response()->json([
            'courseId' => (int) $course->id,
            'chapterId' => (int) $chapter->id,
            'title' => $chapter->title,
            'description' => $chapter->description,
            'credits' => (int) $chapter->credits,
            'content' => $content,
            'quiz' => [
                'questions' => $questions,
            ],
        ]);
    }
}
