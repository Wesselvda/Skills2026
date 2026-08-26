<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Design;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class DesignController extends Controller
{
    public function index()
    {
        $designs = Design::orderBy('name')->get();

        return view('admin.design-symbols', compact('designs'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'design' => ['required', 'file', 'mimes:png', 'max:5120'],
        ]);

        $file = $request->file('design');
        $filename = Str::uuid() . '.png';
        $file->storeAs('design_symbols', $filename, 'public');

        Design::create([
            'name' => pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME),
            'image_filename' => $filename,
            'is_active' => true,
        ]);

        return redirect()->route('admin.design-symbols')->with('success', 'Design symbol uploaded.');
    }

    public function toggleActive(Design $design)
    {
        $design->update(['is_active' => !$design->is_active]);

        return redirect()->route('admin.design-symbols')->with('success', 'Design symbol updated.');
    }

    public function destroy(Design $design)
    {
        if ($design->is_active) {
            return redirect()->route('admin.design-symbols')->with('error', 'Deactivate the design symbol before deleting it.');
        }

        Storage::disk('public')->delete('design_symbols/' . $design->image_filename);
        $design->forceDelete();

        return redirect()->route('admin.design-symbols')->with('success', 'Design symbol deleted.');
    }
}
