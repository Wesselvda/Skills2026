<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ContentPage;

class HeritageController extends Controller
{
    public function index()
    {
        return $this->handleHeritageRequest();
    }

    public function show(string $slug)
    {
        return $this->handleHeritageRequest($slug);
    }

    private function handleHeritageRequest(string $slug = "")
    {
        $result = ContentPage::getFromDirectory($slug, true);

        if ($result instanceof ContentPage) {
            return view('heritages.show', ['contentPage' => $result, 'slug' => $slug]);
        } elseif (is_array($result)) {
            return view('heritages.index', ['contentPages' => $result, 'slug' => $slug]);
        } else {
            abort(404);
        }
    }
}
