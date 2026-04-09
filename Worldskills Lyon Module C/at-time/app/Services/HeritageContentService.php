<?php

namespace App\Services;

use Carbon\CarbonImmutable;

class HeritageContentService
{
    private string $basePath;

    public function __construct()
    {
        $this->basePath = storage_path('app/private/content-pages');
    }

    public function getListing(string $relativePath = ''): array
    {
        $normalizedPath = $this->normalizePath($relativePath);
        $absolutePath = $this->absolutePath($normalizedPath);

        if (!is_dir($absolutePath)) {
            abort(404);
        }

        $folders = [];
        $pages = [];

        foreach (scandir($absolutePath) ?: [] as $entry) {
            if ($entry === '.' || $entry === '..') {
                continue;
            }

            $entryPath = $absolutePath.DIRECTORY_SEPARATOR.$entry;

            if (is_dir($entryPath)) {
                if ($entry === 'images') {
                    continue;
                }

                $folderRelative = ltrim($normalizedPath.'/'.$entry, '/');
                $folders[] = [
                    'name' => $entry,
                    'relative_path' => $folderRelative,
                    'url' => $this->heritageUrl($folderRelative),
                ];

                continue;
            }

            $page = $this->buildVisiblePageRecord($entryPath);
            if ($page === null) {
                continue;
            }

            $pages[] = $this->decoratePageForListing($page);
        }

        usort($folders, [$this, 'compareByName']);
        usort($pages, [$this, 'compareByFilenameDesc']);

        return [
            'relative_path' => $normalizedPath,
            'breadcrumbs' => $this->breadcrumbs($normalizedPath),
            'folders' => $folders,
            'pages' => $pages,
        ];
    }

    public function hasFolder(string $relativePath): bool
    {
        $normalizedPath = $this->normalizePath($relativePath);

        return is_dir($this->absolutePath($normalizedPath));
    }

    public function getPageByRoutePath(string $routePath): ?array
    {
        $normalizedPath = $this->normalizePath($routePath);

        if ($normalizedPath === '') {
            return null;
        }

        $parts = [];
        foreach (explode('/', $normalizedPath) as $part) {
            if ($part !== '') {
                $parts[] = $part;
            }
        }

        if ($parts === []) {
            return null;
        }

        $slug = array_pop($parts);
        $folderPath = implode('/', $parts);
        $folderAbsolute = $this->absolutePath($folderPath);

        if (!is_dir($folderAbsolute)) {
            return null;
        }

        foreach (scandir($folderAbsolute) ?: [] as $entry) {
            if ($entry === '.' || $entry === '..') {
                continue;
            }

            $entryPath = $folderAbsolute.DIRECTORY_SEPARATOR.$entry;
            if (!is_file($entryPath)) {
                continue;
            }

            $page = $this->buildVisiblePageRecord($entryPath);
            if ($page === null) {
                continue;
            }

            if (strcasecmp($page['slug'], $slug) !== 0) {
                continue;
            }

            return $this->hydratePageForRender($page);
        }

        return null;
    }

    public function imageAbsolutePath(string $filename): ?string
    {
        $clean = trim(str_replace('\\', '/', $filename));
        if ($clean === '' || str_contains($clean, '../') || str_starts_with($clean, '/')) {
            return null;
        }

        $path = $this->basePath.DIRECTORY_SEPARATOR.'images'.DIRECTORY_SEPARATOR.$clean;
        if (!is_file($path)) {
            return null;
        }

        return $path;
    }

    public function imageUrl(string $filename): string
    {
        return route('heritages.image', ['filename' => $filename]);
    }

    public function getPagesByTag(string $tag): array
    {
        $needle = trim(mb_strtolower($tag));
        if ($needle === '') {
            return [];
        }

        $pages = [];

        foreach ($this->allVisiblePages() as $page) {
            foreach ($page['tags'] as $pageTag) {
                if (mb_strtolower($pageTag) === $needle) {
                    $pages[] = $this->decoratePageForListing($page);
                    break;
                }
            }
        }

        usort($pages, [$this, 'compareByFilenameDesc']);

        return $pages;
    }

    public function searchPages(string $keywords): array
    {
        $terms = [];
        foreach (explode('/', $keywords) as $term) {
            $normalized = trim(mb_strtolower($term));
            if ($normalized !== '') {
                $terms[] = $normalized;
            }
        }

        if ($terms === []) {
            return [];
        }

        $results = [];

        foreach ($this->allVisiblePages() as $page) {
            $haystackTitle = mb_strtolower($page['title']);

            foreach ($terms as $term) {
                if (str_contains($haystackTitle, $term)) {
                    $results[] = $this->decoratePageForListing($page);
                    break;
                }
            }
        }

        usort($results, [$this, 'compareByFilenameDesc']);

        return $results;
    }

    private function allVisiblePages(): array
    {
        $pages = [];

        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($this->basePath, \RecursiveDirectoryIterator::SKIP_DOTS)
        );

        foreach ($iterator as $file) {
            if (!$file->isFile()) {
                continue;
            }

            $relativeFile = $this->relativePath($file->getPathname());
            if ($relativeFile === null) {
                continue;
            }

            if (str_starts_with($relativeFile, 'images/')) {
                continue;
            }

            $page = $this->buildVisiblePageRecord($file->getPathname());
            if ($page === null) {
                continue;
            }

            $pages[] = $page;
        }

        return $pages;
    }

    private function buildVisiblePageRecord(string $absoluteFilePath): ?array
    {
        $page = $this->buildPageRecord($absoluteFilePath);

        if ($page === null) {
            return null;
        }

        if ($page['date']->isFuture()) {
            return null;
        }

        if ($page['draft'] === true) {
            return null;
        }

        return $page;
    }

    private function buildPageRecord(string $absoluteFilePath): ?array
    {
        if (!is_file($absoluteFilePath)) {
            return null;
        }

        $filename = basename($absoluteFilePath);
        $filenameParts = $this->parsePageFilename($filename);
        if ($filenameParts === null) {
            return null;
        }

        $date = CarbonImmutable::createFromFormat('Y-m-d', $filenameParts['date']);
        if ($date === false) {
            return null;
        }

        $slug = $filenameParts['slug'];
        $extension = $filenameParts['extension'];
        $raw = file_get_contents($absoluteFilePath);
        if ($raw === false) {
            return null;
        }

        $parsed = $this->parseFrontMatter($raw);
        $frontMatter = $parsed['front_matter'];
        $body = $parsed['body'];

        $title = $this->resolveTitle($frontMatter, $body, $slug, $extension);
        $summary = trim((string) ($frontMatter['summary'] ?? ''));
        $tags = $this->parseTags((string) ($frontMatter['tags'] ?? $frontMatter['tag'] ?? ''));
        $draft = $this->toBool($frontMatter['draft'] ?? false);
        $cover = trim((string) ($frontMatter['cover'] ?? ''));
        if ($cover === '') {
            $cover = $slug.'.jpg';
        }

        $relativeFile = $this->relativePath($absoluteFilePath);
        if ($relativeFile === null) {
            return null;
        }

        $relativeDir = trim(str_replace('\\', '/', dirname($relativeFile)), '.');
        $relativeDir = $relativeDir === '.' ? '' : $relativeDir;

        return [
            'filename' => $filename,
            'date' => $date,
            'date_label' => $date->format('Y-m-d'),
            'slug' => $slug,
            'extension' => $extension,
            'title' => $title,
            'summary' => $summary,
            'tags' => $tags,
            'draft' => $draft,
            'cover' => $cover,
            'body' => $body,
            'relative_file' => $relativeFile,
            'relative_dir' => $relativeDir,
        ];
    }

    private function hydratePageForRender(array $page): array
    {
        $rendered = $page['extension'] === 'html'
            ? $page['body']
            : $this->renderTxtContent($page['body']);

        $coverUrl = $this->imageUrl($this->sanitizeImageFilename($page['cover']));
        $folderUrl = $page['relative_dir'] === ''
            ? route('heritages.index')
            : $this->heritageUrl($page['relative_dir']);

        return array_merge($page, [
            'url' => $this->heritageUrl(trim($page['relative_dir'].'/'.$page['slug'], '/')),
            'folder_url' => $folderUrl,
            'rendered_html' => $rendered,
            'cover_url' => $coverUrl,
            'breadcrumbs' => $this->breadcrumbs($page['relative_dir']),
        ]);
    }

    private function decoratePageForListing(array $page): array
    {
        return [
            'filename' => $page['filename'],
            'date_label' => $page['date_label'],
            'title' => $page['title'],
            'summary' => $page['summary'],
            'tags' => $page['tags'],
            'slug' => $page['slug'],
            'relative_dir' => $page['relative_dir'],
            'url' => $this->heritageUrl(trim($page['relative_dir'].'/'.$page['slug'], '/')),
        ];
    }

    private function parseFrontMatter(string $raw): array
    {
        $normalized = str_replace(["\r\n", "\r"], "\n", $raw);
        if (!str_starts_with($normalized, "---\n")) {
            return [
                'front_matter' => [],
                'body' => $raw,
            ];
        }

        $frontMatterEnd = strpos($normalized, "\n---\n", 4);

        if ($frontMatterEnd === false) {
            return [
                'front_matter' => [],
                'body' => $raw,
            ];
        }

        $fmRaw = substr($normalized, 4, $frontMatterEnd - 4);
        $body = substr($normalized, $frontMatterEnd + 5);
        $frontMatter = [];

        foreach ($this->splitLines($fmRaw) as $line) {
            $line = trim($line);
            if ($line === '' || !str_contains($line, ':')) {
                continue;
            }

            [$key, $value] = explode(':', $line, 2);
            $frontMatter[trim(mb_strtolower($key))] = trim($value);
        }

        return [
            'front_matter' => $frontMatter,
            'body' => $body,
        ];
    }

    private function resolveTitle(array $frontMatter, string $body, string $slug, string $extension): string
    {
        $frontMatterTitle = trim((string) ($frontMatter['title'] ?? ''));
        if ($frontMatterTitle !== '') {
            return $frontMatterTitle;
        }

        if ($extension === 'html') {
            $h1 = $this->extractFirstHeading($body, 'h1');
            if ($h1 !== '') {
                return $h1;
            }
        }

        $normalized = trim(str_replace(['-', '_'], ' ', $slug));
        $normalized = $this->collapseWhitespace($normalized);

        return ucwords(mb_strtolower($normalized));
    }

    private function toBool(mixed $value): bool
    {
        if (is_bool($value)) {
            return $value;
        }

        $normalized = mb_strtolower(trim((string) $value));

        return in_array($normalized, ['1', 'true', 'yes', 'on'], true);
    }

    private function parseTags(string $tagString): array
    {
        if ($tagString === '') {
            return [];
        }

        $tags = [];
        foreach (explode(',', $tagString) as $tag) {
            $clean = trim($tag);
            if ($clean !== '') {
                $tags[] = $clean;
            }
        }

        return $tags;
    }

    private function renderTxtContent(string $body): string
    {
        $lines = $this->splitLines($body);
        $html = [];

        foreach ($lines as $line) {
            $trimmed = trim($line);
            if ($trimmed === '') {
                continue;
            }

            if ($this->isImageFilename($trimmed)) {
                $imageUrl = e($this->imageUrl($this->sanitizeImageFilename($trimmed)));
                $html[] = '<img src="'.$imageUrl.'" alt="" loading="lazy">';
                continue;
            }

            $html[] = '<p>'.e($trimmed).'</p>';
        }

        return implode("\n", $html);
    }

    private function sanitizeImageFilename(string $value): string
    {
        $normalized = trim(str_replace('\\', '/', $value));

        return basename($normalized);
    }

    private function parsePageFilename(string $filename): ?array
    {
        $extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        if (!in_array($extension, ['html', 'txt'], true)) {
            return null;
        }

        $name = pathinfo($filename, PATHINFO_FILENAME);
        $parts = explode('-', $name, 4);
        if (count($parts) < 4) {
            return null;
        }

        [$year, $month, $day, $slug] = $parts;
        if (
            strlen($year) !== 4
            || strlen($month) !== 2
            || strlen($day) !== 2
            || !ctype_digit($year)
            || !ctype_digit($month)
            || !ctype_digit($day)
            || $slug === ''
        ) {
            return null;
        }

        return [
            'date' => $year.'-'.$month.'-'.$day,
            'slug' => $slug,
            'extension' => $extension,
        ];
    }

    private function splitLines(string $value): array
    {
        $normalized = str_replace(["\r\n", "\r"], "\n", $value);

        return $normalized === '' ? [] : explode("\n", $normalized);
    }

    private function extractFirstHeading(string $html, string $tagName): string
    {
        $openTag = '<'.$tagName;
        $closeTag = '</'.$tagName.'>';
        $searchOffset = 0;

        while (true) {
            $start = stripos($html, $openTag, $searchOffset);
            if ($start === false) {
                return '';
            }

            $tagClose = strpos($html, '>', $start);
            if ($tagClose === false) {
                return '';
            }

            $end = stripos($html, $closeTag, $tagClose + 1);
            if ($end === false) {
                return '';
            }

            $content = trim(html_entity_decode(strip_tags(substr($html, $tagClose + 1, $end - $tagClose - 1))));
            if ($content !== '') {
                return $content;
            }

            $searchOffset = $end + strlen($closeTag);
        }
    }

    private function collapseWhitespace(string $value): string
    {
        $value = str_replace(["\t", "\n", "\r", "\f", "\v"], ' ', $value);

        while (str_contains($value, '  ')) {
            $value = str_replace('  ', ' ', $value);
        }

        return trim($value);
    }

    private function isImageFilename(string $value): bool
    {
        $extension = strtolower(pathinfo($value, PATHINFO_EXTENSION));

        return in_array($extension, ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg'], true);
    }

    private function absolutePath(string $relativePath): string
    {
        $full = $this->basePath;
        if ($relativePath !== '') {
            $full .= DIRECTORY_SEPARATOR.str_replace('/', DIRECTORY_SEPARATOR, $relativePath);
        }

        return $full;
    }

    private function relativePath(string $absolutePath): ?string
    {
        $normalizedBase = str_replace('\\', '/', realpath($this->basePath) ?: $this->basePath);
        $normalizedPath = str_replace('\\', '/', realpath($absolutePath) ?: $absolutePath);

        if (!str_starts_with($normalizedPath, $normalizedBase.'/') && $normalizedPath !== $normalizedBase) {
            return null;
        }

        $relative = substr($normalizedPath, strlen($normalizedBase));

        return ltrim((string) $relative, '/');
    }

    public function normalizePath(string $value): string
    {
        $decoded = urldecode(trim($value));
        $decoded = str_replace('\\', '/', $decoded);
        $parts = [];
        foreach (explode('/', $decoded) as $part) {
            if ($part !== '') {
                $parts[] = $part;
            }
        }

        $safeParts = [];
        foreach ($parts as $part) {
            if ($part === '.' || $part === '..') {
                continue;
            }

            $safeParts[] = $part;
        }

        return implode('/', $safeParts);
    }

    private function heritageUrl(string $path): string
    {
        return route('heritages.path', ['path' => $path]);
    }

    private function breadcrumbs(string $relativePath): array
    {
        if ($relativePath === '') {
            return [];
        }

        $items = [];
        $parts = explode('/', $relativePath);
        $acc = [];

        foreach ($parts as $part) {
            $acc[] = $part;
            $items[] = [
                'name' => $part,
                'url' => $this->heritageUrl(implode('/', $acc)),
            ];
        }

        return $items;
    }

    private function compareByName(array $a, array $b): int
    {
        return strcasecmp($a['name'], $b['name']);
    }

    private function compareByFilenameDesc(array $a, array $b): int
    {
        return strcasecmp($b['filename'], $a['filename']);
    }

}
