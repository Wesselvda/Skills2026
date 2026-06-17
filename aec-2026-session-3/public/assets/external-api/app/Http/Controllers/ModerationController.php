<?php

namespace App\Http\Controllers;

use App\Traits\DeterministicRandom;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Knuckles\Scribe\Attributes\BodyParam;
use Knuckles\Scribe\Attributes\Endpoint;
use Knuckles\Scribe\Attributes\Group;
use Knuckles\Scribe\Attributes\Response;

#[Group('Moderation', description: 'Content moderation services for reviewing user-generated content')]
class ModerationController extends Controller
{
    use DeterministicRandom;

    protected array $categories = [
        'mild_swearing',
        'internet_being_unnecessarily_aggressive',
        'heated_comment_section_energy',
        'would_start_a_discord_argument',
        'might_get_you_banned_in_a_group_chat'
    ];

    #[Endpoint(
        title: 'Check content',
        description: 'Analyzes content for sensitive or inappropriate material and returns flagged words with their positions.'
    )]
    #[BodyParam('text', required: true, description: 'The content to moderate (minimum 1 character)', example: 'This is a sample review text')]
    #[Response([
        'flaggedWords' => [
            ['start' => 5, 'category' => 'profanity'],
        ],
    ], 200, description: 'Flagged words')]
    #[Response([
        'message' => 'Validation failed',
        'errors' => ['text' => ['The text field is required.']],
    ], 422)]
    public function check(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'text' => 'required|string|min:1',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $content = $request->input('text', '');

        if (empty($content)) {
            return response()->json([
                'flaggedWords' => [],
            ]);
        }

        preg_match_all('/[a-zA-Z]{3,}/', $content, $matches, PREG_OFFSET_CAPTURE);

        $words = $matches[0];
        $flaggedWords = [];

        foreach ($words as $word) {
            $wordText = $word[0];
            $startPos = $word[1];

            $seed = $content.$wordText.":".$startPos;

            if ($this->deterministicChance($seed) < 0.2) {
                $category = $this->categories[$this->deterministicRandom($seed) % count($this->categories)];
                $flaggedWords[] = [
                    'start' => $startPos,
                    'category' => $category,
                ];
            }
        }

        return response()->json([
            'flaggedWords' => $flaggedWords,
        ]);
    }
}
