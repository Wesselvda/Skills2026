<?php

namespace App\Http\Controllers;

class MentorController extends Controller
{
    public function index()
    {
        return view('admin.wip', [
            'title' => 'Mentor Management'
        ]);
    }
}
