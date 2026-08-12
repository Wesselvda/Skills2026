<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;

class ContentPage
{
    public string $fileName;
    public string $content;
    public string $parsedContent = '';
    public bool $isVisible = true;

    // Front matter
    public string $title = '';
    public array $tags = [];
    public string $cover = '';
    public string $summary = '';
    public bool $isDraft = false;

    public ?Carbon $contentDate;

    public function __construct(string $filename)
    {
        $this->fileName = $filename;
        $this->content = Storage::disk("public")->get("content-pages/{$filename}");
        $this->init();
    }

    public function init()
    {
        $this->parseFrontMatter();
        $this->parsedContentDate();

        if (empty($this->title)) {
            $this->title = $this->getTitle();
        }

        $this->isVisible = !$this->isDraft && ($this->contentDate !== null && $this->contentDate->isPast());
    }

    public function getTitle()
    {
        $lines = explode("\n", $this->content);

        for ($i = 0; $i < count($lines); $i++) {
            if (!empty(trim($lines[$i]))) {
                $line = trim($lines[$i]);
                if (str_starts_with($line, '<h1>') && str_ends_with($line, '</h1>')) {
                    return trim(substr($line, 4, -5));
                }
            }
        }

        $title = substr(basename($this->fileName), 11);
        $title = str_replace('-', ' ', $title);
        $title = ucwords($title);

        return $title;
    }

    public function parsedContentDate()
    {
        $dateString = substr(basename($this->fileName), 0, 10);

        if (!Carbon::hasFormat($dateString, 'Y-m-d')) {
            $this->contentDate = null;
            return;
        }

        $this->contentDate = Carbon::createFromFormat('Y-m-d', $dateString);
    }

    public function parseFrontMatter()
    {
        $content = $this->content;
        $this->parsedContent = $content;

        if (str_starts_with($content, '---')) {
            $parts = explode('---', $content, 3);
            if (count($parts) === 3) {
                $this->parsedContent = $parts[2];
            }
            if (str_contains($parts[1], ':')) {
                $lines = explode("\n", trim($parts[1]));

                foreach ($lines as $line) {
                    [$key, $value] = explode(':', $line, 2);

                    switch (trim($key)) {
                        case 'title':
                            $this->title = trim($value);
                            break;
                        case 'tags':
                            $this->tags = array_map('trim', explode(',', trim($value)));
                            break;
                        case 'cover':
                            $this->cover = trim($value);
                            break;
                        case 'summary':
                            $this->summary = trim($value);
                            break;
                        case 'draft':
                            $this->isDraft = trim($value) === 'true';
                        default:
                            break;
                    }
                }
            }
        }
    }

    // Static functions

    public static function getFromDirectory(string $directory, bool $recursive = false)
    {
        $directory = trim($directory, '/');
        $file = Storage::disk("public")->path("content-pages/{$directory}");

        if (is_file($file)) {
            return new ContentPage($directory);
        }

        $directoryPaths = array_filter(Storage::disk("public")->directories("content-pages/{$directory}"), function ($dir) {
            if (basename($dir) === 'images') {
                return false;
            }
            return true;
        });

        usort($directoryPaths, function ($a, $b) {
            return strcasecmp(basename($a), basename($b));
        });

        $directories = array_map(function ($dir) use ($directory, $recursive) {
            return new ContentDirectory(trim("$directory/" . basename($dir), '/'), $recursive);
        }, $directoryPaths);
        

        $files = array_map(function ($file) use ($directory) {
            return new ContentPage(trim("$directory/" . basename($file), '/'));
        }, Storage::disk("public")->files("content-pages/{$directory}"));

        // Subfolders are listed in alphabetical order, followed by content pages in reverse chronological order (most recent at the top).

        // Filter on isVisible
        $files = array_filter($files, function ($file) {
            return $file->isVisible;
        });

        usort($files, function ($a, $b) {
            return $b->contentDate->timestamp <=> $a->contentDate->timestamp;
        });

        return array_merge($directories, $files);
    }
}

class ContentDirectory
{
    public string $fileName;
    public string $filePath;
    public array $contentPages = [];

    public function __construct(string $filePath, bool $recursive = false)
    {
        $this->filePath = $filePath;
        $this->fileName = basename($filePath);

        if ($recursive) {
            $this->contentPages = ContentPage::getFromDirectory($filePath, true);
        }
    }
}
