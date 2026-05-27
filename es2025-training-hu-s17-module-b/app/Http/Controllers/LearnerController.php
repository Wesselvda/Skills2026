<?php

namespace App\Http\Controllers;

class LearnerController extends Controller
{
    public function index()
    {
        return view('admin.wip', [
            'title' => 'Learner Management'
        ]);
    }
}
