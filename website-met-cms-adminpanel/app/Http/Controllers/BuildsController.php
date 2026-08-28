<?php

namespace App\Http\Controllers;

use App\Models\Build;
use App\Models\Page;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class BuildsController extends Controller
{
    public function showBuild($slug) {
        $build = Build::where('slug', $slug)->first();
        if (!$build) abort(404);
        if (!$build->active) abort(404);
        
        $navigation = Page::where('show_in_navigation', true)->get();

        return view('build', compact('build', 'navigation'));
    }

    public function showAdminBuildPage(Request $request)
    {
        $search = $request->input('search');
        $sort = $request->input('sort');
        $status = $request->input('status');

        $builds = Build::query();

        if ($search) {
            $search = strtolower($search);
            $builds->where(function ($query) use ($search) {
                $query->where('title', 'like', '%'.$search.'%')
                    ->orWhere('intro', 'like', '%'.$search.'%')
                    ->orWhere('description', 'like', '%'.$search.'%');
            });
        }

        switch ($sort) {
            case 'title_desc':
                $builds->orderBy('title', 'DESC');
                break;

            case 'creation_asc':
                $builds->orderBy('created_at', 'ASC');
                break;

            case 'creation_desc':
                $builds->orderBy('created_at', 'DESC');
                break;

            default:
                // Default to title_asc
                $builds->orderBy('title', 'ASC');
                break;
        }

        if ($status) {
            if ($status === 'active') {
                $builds->where('active', true);
            }
            if ($status === 'inactive') {
                $builds->where('active', false);
            }
        }

        $builds = $builds->paginate(10)->withQueryString();

        return view('admin.builds', compact('builds', 'search', 'sort', 'status'));
    }

    public function showEditBuild(Build $build)
    {
        return view('admin.buildeditor', compact('build'));
    }

    public function editBuild(Request $request, Build $build)
    {
        $this->updateBuild($request, $build);

        return redirect()->to('/admin/builds');
    }

    public function showAddBuild()
    {
        return view('admin.buildeditor');
    }

    public function deleteBuild(Build $build)
    {
        $build->delete();

        return redirect()->to('/admin/builds');
    }

    public function addBuild(Request $request)
    {
        $request->validate([
            'thumbnail_filename' => 'mimes:jpg,jpeg,png|max:5000|image|dimensions:min_width=400,max_width=1200,min_height=400,max_height=1200|required',
            'background_filename' => 'mimes:jpg,jpeg,png|max:5000|image|dimensions:min_width=1280,max_width=2560,min_height=720,max_height=1440|required',
            'signature_filename' => 'mimes:jpg,jpeg,png|max:5000|image|required',
        ]);

        $build = new Build;

        $this->updateBuild($request, $build);

        return redirect()->to('/admin/builds');
    }

    public function updateBuild(Request $request, Build $build)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:40',
            'intro' => 'required|string|max:200',
            'description' => 'required|string|max:65535',
            'active' => 'nullable',
            'thumbnail_filename' => 'mimes:jpg,jpeg,png|max:5000|image|dimensions:min_width=400,max_width=1200,min_height=400,max_height=1200|nullable',
            'background_filename' => 'mimes:jpg,jpeg,png|max:5000|image|dimensions:min_width=1280,max_width=2560,min_height=720,max_height=1440|nullable',
            'signature_filename' => 'mimes:jpg,jpeg,png|max:5000|image|nullable',
        ]);

        if ($request->hasFile('thumbnail_filename')) {
            $image = $request->image('thumbnail_filename');

            $minSize = min($image->width(), $image->height());
            $filename = $image->hashName();
            $path = $image->storePublicly('build-thumbnail-images', 'public');
            $type = str_contains(Storage::mimeType($path), 'png') ? 'png' : 'jpg';

            $processedPath = $this->addTextWatermark(
                $filename,
                '/app/public/' . $path,
                $minSize,
                $type,
                "Wessel 2026",
            );

            $build->thumbnail_filename = $processedPath;
        }

        if ($request->hasFile('background_filename')) {
            $image = $request->image('background_filename');
            $path = $image->storePublicly('build-background-images', 'public');

            $build->background_filename = $path;
        }

        if ($request->hasFile('signature_filename')) {
            $image = $request->image('signature_filename');
            $path = $image->storePublicly('build-signature-images', 'public');

            $build->signature_filename = $path;
        }

        $build->title = $validated['title'];
        $build->slug = $this->generateSlug($build->slug, $validated['title']);

        $build->intro = $validated['intro'];
        $build->description = $validated['description'];

        if (array_key_exists('active', $validated)) {
            $build->active = $validated['active'] == 'on';
        } else {
            $build->active = false;
        }

        $build->save();

        return redirect()->to('/admin/builds');
    }

    private function addTextWatermark(string $filename, string $path, int $minSize, string $type, string $watermark): string
    {
        $processedImage = null;

        if ($type === "png") {
            $processedImage = imagecreatefrompng(str_replace('/', '\\', storage_path($path)));
        } else {
            $processedImage = imagecreatefromjpeg(str_replace('/', '\\', storage_path($path)));
        }

        $font = 5;
        $padding = 10;
        $textWidth = imagefontwidth($font) * strlen($watermark);
        $textHeight = imagefontheight($font);
        $x = imagesx($processedImage) - $textWidth - $padding;
        $y = imagesy($processedImage) - $textHeight - $padding;

        $background = imagecolorallocatealpha($processedImage, 0, 0, 0, 55);
        $textColor = imagecolorallocate($processedImage, 255, 255, 255);

        imagefilledrectangle(
            $processedImage,
            $x - $padding,
            $y - $padding,
            imagesx($processedImage),
            imagesy($processedImage),
            $background,
        );
        imagestring($processedImage, $font, $x, $y, $watermark, $textColor);

        $returnedPath = storage_path() . '/app/public/build-thumbnail-images/processed/' . $filename;

        if ($type === "png") {
            imagepng($processedImage, $returnedPath );
        } else {
            imagejpeg($processedImage, $returnedPath );
        }

        return "/build-thumbnail-images/processed/$filename";
    }

    private function generateSlug(string|null $currentSlug, string $title)
    {
        // Replace spaces with dashes and make it lowercase
        $replacedTitle = strtolower(str_replace(' ', '-', $title));

        $allowedCharacters = 'abcdefghijklmnopqrstuvwxyz01234567890-';

        $newSlug = '';

        // Filter characters
        foreach (str_split($replacedTitle) as $character) {
            if (str_contains($allowedCharacters, $character)) {
                $newSlug .= $character;
            }
        }

        // Remove double dashes
        while (str_contains($newSlug, '--')) {
            $newSlug = str_replace('--', '-', $newSlug);
        }

        // Remove first and last dash if they exist
        $newSlug = ltrim(rtrim($newSlug, '-'), '-');

        // If the slug is still the same, return it.
        if ($currentSlug === $newSlug) {
            return $newSlug;
        }

        // If the slug isn't the same, validate if its unique, and if it isnt make it unique.
        $checkingSlug = $newSlug;
        $increment = 1;

        while (Build::where('slug', $checkingSlug)->first()) {
            $increment++;
            $checkingSlug = $newSlug.'-'.$increment;
        }

        return $checkingSlug;
    }
}
