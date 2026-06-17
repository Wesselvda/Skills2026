<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class Translation extends Model
{
    use HasUuids;

    protected $fillable = [
        'message_key',
        'language',
        'message',
    ];

    public static function findByMessageAndLanguage(string $message, string $language): ?self
    {
        return self::where('language', $language)
            ->where('message', $message)
            ->first();
    }

    public static function findTranslationByMessageKeyAndLanguage(string $messageKey, string $language): ?self
    {
        return self::where('message_key', $messageKey)
            ->where('language', $language)
            ->first();
    }
}
