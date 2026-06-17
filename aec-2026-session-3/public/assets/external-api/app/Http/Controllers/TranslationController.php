<?php

namespace App\Http\Controllers;

use App\Models\Translation;
use App\Traits\DeterministicRandom;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Knuckles\Scribe\Attributes\BodyParam;
use Knuckles\Scribe\Attributes\Endpoint;
use Knuckles\Scribe\Attributes\Group;
use Knuckles\Scribe\Attributes\Response;

#[Group('Translation', description: 'Content translation services for multi-language support')]
class TranslationController extends Controller
{
    use DeterministicRandom;

    private function translateSingle(string $text, string $sourceLanguage, string $targetLanguage): ?string
    {
        if ($this->deterministicChance('failure:'.$text.':'.$sourceLanguage.':'.$targetLanguage) < 0.15) {
            return null;
        }

        $sourceTranslation = Translation::findByMessageAndLanguage($text, $sourceLanguage);
        if (! $sourceTranslation) {
            return null;
        }

        $targetTranslation = Translation::findTranslationByMessageKeyAndLanguage(
            $sourceTranslation->message_key,
            $targetLanguage
        );

        return $targetTranslation?->message;
    }

    #[Endpoint(
        title: 'Translate text',
        description: 'Translates text from a source language to a target language.'
    )]
    #[BodyParam('text', required: true, description: 'The text to translate', example: 'Hello')]
    #[BodyParam('sourceLanguage', required: true, description: 'The source language code (en, de, nl, hu)', example: 'en')]
    #[BodyParam('targetLanguage', required: true, description: 'The target language code (en, de, nl, hu)', example: 'de')]
    #[Response(['translation' => 'Hallo'], 200, description: 'Success')]
    #[Response(['message' => 'Could not translate the text'], 404, description: 'Translation failed')]
    #[Response(status: 422, description: 'Validation error')]
    public function translate(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'text' => 'required|string',
            'sourceLanguage' => 'required|string',
            'targetLanguage' => 'required|string',
        ]);

        $result = $this->translateSingle(
            $validated['text'],
            $validated['sourceLanguage'],
            $validated['targetLanguage']
        );

        if (! $result) {
            return response()->json(
                ['message' => 'Could not translate the text'],
                404
            );
        }

        return response()->json(['translation' => $result]);
    }

    #[Endpoint(
        title: 'Batch translate texts',
        description: 'Translates multiple texts from source languages to target languages.'
    )]
    #[BodyParam('texts', required: true, description: 'Array of translation requests', example: [['text' => 'Hello', 'sourceLanguage' => 'en', 'targetLanguage' => 'de']])]
    #[BodyParam('texts[].text', required: true, description: 'The text to translate', example: 'Hello')]
    #[BodyParam('texts[].sourceLanguage', required: true, description: 'The source language code', example: 'en')]
    #[BodyParam('texts[].targetLanguage', required: true, description: 'The target language code', example: 'de')]
    #[Response([
        ['success' => true, 'translation' => 'Hallo'],
        ['success' => false, 'message' => 'Could not translate the text'],
    ], 200, description: 'Batch results')]
    #[Response(status: 422, description: 'Validation error')]
    public function batch(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'texts' => 'required|array|min:1',
            'texts.*.text' => 'required|string',
            'texts.*.sourceLanguage' => 'required|string',
            'texts.*.targetLanguage' => 'required|string',
        ]);

        $results = [];

        foreach ($validated['texts'] as $translationRequest) {
            $result = $this->translateSingle(
                $translationRequest['text'],
                $translationRequest['sourceLanguage'],
                $translationRequest['targetLanguage']
            );

            if ($result) {
                $results[] = [
                    'success' => true,
                    'translation' => $result,
                ];
            } else {
                $results[] = [
                    'success' => false,
                    'message' => 'Could not translate the text',
                ];
            }
        }

        return response()->json($results);
    }
}
