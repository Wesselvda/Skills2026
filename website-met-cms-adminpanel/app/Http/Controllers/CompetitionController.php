<?php

namespace App\Http\Controllers;

use App\Models\Competition;
use Illuminate\Http\Request;

class CompetitionController extends Controller
{
    public function showAdminCompetitionPage()
    {
        $competitions = Competition::all();

        return view('admin.competitions', compact('competitions'));
    }

    public function showAddCompetition()
    {
        return view('admin.competitioneditor');
    }

    public function showEditCompetition(Competition $competition)
    {
        return view('admin.competitioneditor', compact('competition'));
    }

    public function addCompetition(Request $request)
    {
        if (Competition::count() >= 10) {
            return redirect()->back()->withErrors(['limit' => 'A maximum of 10 competitions is allowed.']);
        }

        $request->validate([
            'image_filename' => 'mimes:jpg,jpeg,png|max:5000|image|required',
        ]);

        $competition = new Competition;

        $this->updateCompetition($request, $competition);

        return redirect()->to('/admin/competitions');
    }

    public function editCompetition(Request $request, Competition $competition)
    {
        $this->updateCompetition($request, $competition);

        return redirect()->to('/admin/competitions');
    }

    public function deleteCompetition(Competition $competition)
    {
        $competition->delete();

        return redirect()->to('/admin/competitions');
    }

    private function updateCompetition(Request $request, Competition $competition)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:40',
            'text' => 'required|string|max:150',
            'active' => 'nullable',
            'image_filename' => 'mimes:jpg,jpeg,png|max:5120|image|nullable',
            'position' => 'required|integer|in:1,2,3',
        ]);

        if ($request->hasFile('image_filename')) {
            $image = $request->image('image_filename');
            $path = $image->storePublicly('competition-images', 'public');

            $competition->image_filename = $path;
        }

        $competition->title = $validated['title'];
        $competition->text = $validated['text'];
        $competition->position = $validated['position'];
        $competition->active = array_key_exists('active', $validated) && $validated['active'] === 'on';
        $competition->save();
    }
}
