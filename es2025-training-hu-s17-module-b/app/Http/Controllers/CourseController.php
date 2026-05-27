<?php

namespace App\Http\Controllers;

class CourseController extends Controller
{
    public function index()
    {
        return view('admin.wip', [
            'title' => 'Course Management'
        ]);
    }

    public function create()
    {
        return view('admin.wip', [
            'title' => 'Create Course'
        ]);
    }

    public function edit($course = null)
    {
        return view('admin.wip', [
            'title' => 'Edit Course'
        ]);
    }
}
