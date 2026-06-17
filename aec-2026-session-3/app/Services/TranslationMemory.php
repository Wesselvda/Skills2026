<?php

namespace App\Services;

class TranslationMemory
{
    private static ?array $byMessage = null;

    public function translate(string $text, string $sourceLanguage, string $targetLanguage)
    {
        if ($sourceLanguage === $targetLanguage) {
            return [
                'success' => true,
                'translation' => $text,
            ];
        }

        if ($this->chance('failure:'.$text.':'.$sourceLanguage.':'.$targetLanguage) < 0.15) {
            return [
                'success' => false,
                'translation' => null,
            ];
        }

        $message = self::messages()[$text][$sourceLanguage] ?? null;

        if (! $message) {
            return [
                'success' => false,
                'translation' => null,
            ];
        }

        $translation = self::messagesByKey()[$message][$targetLanguage] ?? null;

        return [
            'success' => $translation !== null,
            'translation' => $translation,
        ];
    }

    private static function messages()
    {
        if (self::$byMessage !== null) {
            return self::$byMessage;
        }

        self::$byMessage = [];
        self::$byKey = [];
        $path = base_path('public/assets/external-api/database/seeders/data/translations.json');
        $rows = json_decode(file_get_contents($path), true);

        foreach ($rows as $row) {
            self::$byMessage[$row['message']][$row['language']] = $row['message_key'];
            self::$byKey[$row['message_key']][$row['language']] = $row['message'];
        }

        return self::$byMessage;
    }

    private static ?array $byKey = null;

    private static function messagesByKey()
    {
        self::messages();

        return self::$byKey;
    }

    private function chance(string $key)
    {
        $seed = 'rng:TranslationController:'.$key;

        return ((hexdec(substr(md5($seed), 0, 8)) & 0x7FFFFFFF) % 100) / 100.0;
    }
}
