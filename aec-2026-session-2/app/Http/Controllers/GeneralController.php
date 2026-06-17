<?php

namespace App\Http\Controllers;

class GeneralController extends Controller
{
    public function index()
    {
        if (session()->has('user_id')) {
            return redirect()->route('submissions.index');
        }

        return redirect()->route('login');
    }
}
