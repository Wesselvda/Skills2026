<?php

namespace App\Http\Controllers;

use App\Models\InvestmentRequest;
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

    public function admin()
    {
        $investments = InvestmentRequest::with('turbine', 'supports', 'presentingSponsor')->orderBy('created_at', 'desc')->get();
        return view('admin.admin', compact('investments'));
    }
}
