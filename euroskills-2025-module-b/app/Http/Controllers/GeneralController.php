<?php

namespace App\Http\Controllers;

use App\Models\MockEmail;
use Illuminate\Http\Request;

class GeneralController extends Controller
{
    public function home()
    {
        return view('home');
    }

    public function mockEmails()
    {
        $mockEmails = MockEmail::orderBy('created_at', 'desc')->get();

        return view('mock-emails', compact('mockEmails'));
    }
}
