<?php

namespace App\Http\Controllers;

use App\Models\Build;
use App\Models\Competition;
use App\Models\Page;
use Illuminate\Http\Request;

class PageController extends Controller
{
    public function viewPage(string $slug) {
        $page = Page::where('slug', $slug)->first();
        if (!$page) {
            abort(404);
        }

        $navigation = Page::where('show_in_navigation', true)->get();

        if ($page->tag === "builds") {
            $builds = Build::where('active', true)->get();
            return view('page', compact('navigation', 'page', 'builds'));
        }

        if ($page->tag === "about") {
            $competitions = Competition::where('active', true)->get();
            return view('page', compact('navigation', 'page', 'competitions'));
        }

        return view('page', compact('navigation', 'page'));
    }

    public function showAdminPages() {
        $pages = Page::all();

        return view('admin.pages', compact('pages'));
    }

    public function showPageEditor(Page $page) {
        return view('admin.pageeditor', compact('page'));
    }

    public function updatePage(Request $request, Page $page) {
        $validated = $request->validate([
            'title' => 'required|string|max:40',
            'intro' => 'required|string|max:200',
            'description' => 'required|string|max:65535',
            'show_in_navigation' => 'nullable',
            'image_filename' => 'mimes:jpg,jpeg,png|max:5000|image|dimensions:min_width=1280,max_width=2560,min_height=720,max_height=1440|nullable'
        ]);

        if ($request->hasFile('image_filename')) {
            $image = $request->image('image_filename');
            $path = $image->storePublicly('page-images', 'public');

            $page->image_filename = $path;
        }

        $page->title = $validated['title'];
        $page->slug = $this->generateSlug($page->slug, $validated['title']);

        $page->intro = $validated['intro'];
        $page->description = $validated['description'];

        if (key_exists('show_in_navigation', $validated)) {
            $page->show_in_navigation = $validated['show_in_navigation'] == "on";
        } else {
            $page->show_in_navigation = false;
        }

        $page->save();

        return redirect()->to('/admin/pages');
    }

    private function generateSlug(string $currentSlug, string $title) {
        // Replace spaces with dashes and make it lowercase
        $replacedTitle = strtolower(str_replace(' ', '-', $title));

        $allowedCharacters = "abcdefghijklmnopqrstuvwxyz01234567890-";

        $newSlug = "";

        // Filter characters
        foreach (str_split($replacedTitle) as $character) {
            if (str_contains($allowedCharacters, $character)) {
                $newSlug .= $character;
            }
        }

        // Remove double dashes
        while(str_contains($newSlug, '--')) {
            $newSlug = str_replace('--', '-', $newSlug);
        }

        // Remove first and last dash if they exist
        $newSlug = ltrim(rtrim($newSlug, '-'), '-');

        // If the slug is still the same, return it.
        if ($currentSlug === $newSlug) return $newSlug;

        // If the slug isn't the same, validate if its unique, and if it isnt make it unique.
        $checkingSlug = $newSlug;
        $increment = 1;

        while (Page::where('slug', $checkingSlug)->first()) {
            $increment++;
            $checkingSlug = $newSlug . '-' . $increment;
        }

        return $checkingSlug;
    }
}
