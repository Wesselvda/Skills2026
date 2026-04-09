<?php

namespace App\Http\Controllers;

use App\Services\HeritageContentService;
use Illuminate\Contracts\View\View;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class HeritageController extends Controller
{
    public function __construct(private readonly HeritageContentService $contentService)
    {
    }

    public function index(): View
    {
        $listing = $this->contentService->getListing('');

        return view('heritages.list', [
            'pageTitle' => 'Lyon Heritage Sites',
            'metaDescription' => 'Overview of all heritages.',
            'listingTitle' => 'All Heritages',
            'listing' => $listing,
            'pages' => $listing['pages'],
        ]);
    }

    public function heritagePath(string $path): View
    {
        $normalized = $this->contentService->normalizePath($path);

        if ($this->contentService->hasFolder($normalized)) {
            $listing = $this->contentService->getListing($normalized);

            return view('heritages.list', [
                'pageTitle' => 'Folder: '.$normalized,
                'metaDescription' => 'Folder overview of '.$normalized,
                'listingTitle' => 'Folder: '.$normalized,
                'listing' => $listing,
                'pages' => $listing['pages'],
            ]);
        }

        $page = $this->contentService->getPageByRoutePath($normalized);
        if ($page === null) {
            abort(404);
        }

        return view('heritages.show', [
            'pageTitle' => $page['title'],
            'metaDescription' => $page['summary'] !== '' ? $page['summary'] : 'Heritage detail page',
            'metaImage' => $page['cover_url'],
            'page' => $page,
        ]);
    }

    public function tag(string $tag): View
    {
        $decodedTag = urldecode($tag);
        $pages = $this->contentService->getPagesByTag($decodedTag);

        return view('heritages.list', [
            'pageTitle' => 'Tag: '.$decodedTag,
            'metaDescription' => 'All pages with tag '.$decodedTag,
            'listingTitle' => 'Tag: '.$decodedTag,
            'listing' => [
                'relative_path' => '',
                'breadcrumbs' => [],
                'folders' => [],
            ],
            'pages' => $pages,
            'searchKeywords' => '',
        ]);
    }

    public function search(?string $keywords = null): View
    {
        $query = trim((string) ($keywords ?? request()->query('q', '')));
        $pages = $query !== '' ? $this->contentService->searchPages($query) : [];

        return view('heritages.list', [
            'pageTitle' => 'Search',
            'metaDescription' => 'Search on title and content.',
            'listingTitle' => 'Search: '.$query,
            'listing' => [
                'relative_path' => '',
                'breadcrumbs' => [],
                'folders' => [],
            ],
            'pages' => $pages,
            'searchKeywords' => $query,
        ]);
    }

    public function image(string $filename): BinaryFileResponse
    {
        $decoded = urldecode($filename);
        $absolutePath = $this->contentService->imageAbsolutePath($decoded);
        if ($absolutePath === null) {
            abort(404);
        }

        return response()->file($absolutePath);
    }
}
