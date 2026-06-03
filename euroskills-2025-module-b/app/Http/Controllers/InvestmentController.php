<?php

namespace App\Http\Controllers;

use App\Models\InvestmentRequest;
use App\Models\MockEmail;
use App\Models\PresentingSponsor;
use App\Models\Support;
use App\Models\Turbine;
use Illuminate\Http\Request;
use Pest\Plugins\Tia\Storage;

class InvestmentController extends Controller
{
    public function index()
    {
        $turbines = Turbine::all();
        $totalInvestment = Support::where('status', 'approved')->sum('amount');

        return view('invest.invest', compact('turbines', 'totalInvestment'));
    }

    public function handleInvest(Request $request)
    {
        $validated = $request->validate([
            'fullname' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'address' => 'required|string|max:255',
            'phone' => 'required|string|max:20',
            'investment_option' => 'required|string|in:turbine,presenting,support',
            'turbine_option' => 'nullable|exists:turbines,id',
            'turbine_text' => 'nullable|string|max:25',
            'turbine_logo' => 'nullable|image|max:1024', // Max 1MB
            'sponsor_logo' => 'nullable|image|max:1024', // Max 1MB
            'donation_amount' => 'nullable|numeric|min:0.01',
        ]);

        $turbineData = null;
        $sponsorLogoData = null;

        if ($validated['investment_option'] === 'turbine') {
            if (isset($validated['turbine_option'])) {
                $turbine = Turbine::find($validated['turbine_option']);

                if ($turbine->status !== 'available') {
                    return back()->withErrors(['turbine_option' => 'Selected turbine is not available.']);
                }

                if (isset($validated['turbine_text']) || isset($validated['turbine_logo'])) {
                    $turbineData = [
                        'turbine' => $turbine,
                        'displayed_text' => $validated['turbine_text'] ?? null,
                    ];

                    if ($request->hasFile('turbine_logo')) {
                        $file = $request->file('turbine_logo');
                        $path = $file->store('sponsorImages', 'public');
                        $turbineData['logo_filename'] = basename($path);
                    };
                } else {
                    return back()->withErrors(['turbine_text' => 'Please provide either a text or upload a logo.']);
                }
            } else {
                return back()->withErrors(['turbine_option' => 'Please select a turbine to fund.']);
            }
        } elseif ($validated['investment_option'] === 'presenting') {
            if ($request->hasFile('sponsor_logo')) {
                $file = $request->file('sponsor_logo');
                $path = $file->store('sponsorImages', 'public');
                $sponsorLogoFilename = basename($path);

                $sponsorLogoData = [
                    'logo_filename' => $sponsorLogoFilename,
                ];
            } else {
                return back()->withErrors(['sponsor_logo' => 'Please upload a sponsor logo.']);
            }
        } elseif ($validated['investment_option'] === 'support') {
            if (!isset($validated['donation_amount'])) {
                return back()->withErrors(['donation_amount' => 'Please enter a donation amount.']);
            }
        } else {
            return back()->withErrors(['investment_option' => 'Invalid investment option selected.']);
        }

        $investment = InvestmentRequest::create([
            'investor_name' => $validated['fullname'],
            'investor_email' => $validated['email'],
            'investor_address' => $validated['address'],
            'investor_phone' => $validated['phone'],
            'investment_type' => $validated['investment_option'],
        ]);

        if ($turbineData) {
            $turbineData['turbine']->update([
                'investment_id' => $investment->id,
                'status' => 'pending',
                'displayed_text' => $turbineData['displayed_text'] ?? null,
                'logo_filename' => $turbineData['logo_filename'] ?? null,
            ]);
        } elseif ($sponsorLogoData) {
            $investment->presentingSponsor()->create([
                'logo_filename' => $sponsorLogoData['logo_filename'],
                'status' => 'pending',
            ]);
        } elseif ($validated['investment_option'] === 'support') {
            Support::create([
                'investment_id' => $investment->id,
                'amount' => $validated['donation_amount'],
                'status' => 'pending',
            ]);
        }

        $investmentReference = "INV-".str_pad($investment->id, 8, '0', STR_PAD_LEFT);
        $investmentType = ucfirst($validated['investment_option']);
        $supportAmount = $validated['investment_option'] === 'support' ? number_format($validated['donation_amount'], 2) . ' DKK' : null;
        $name = $validated['fullname'];

        MockEmail::create([
            'subject' => 'Investment Request Confirmation #' . $investmentReference,
            'recipient' => $validated['email'],
            'body' => view('emails.investment-confirmation', compact(
                'name',
                'investmentReference',
                'investmentType',
                'turbineData',
                'sponsorLogoData',
                'supportAmount'
            ))->render(),
        ]);

        return view('invest.email-sent');
    }

    public function sponsorPage() {
        $sponsors = PresentingSponsor::where('status', 'approved')->get();

        return view('invest.sponsors', compact('sponsors'));
    }

    public function approveInvestment($id)
    {
        $investment = InvestmentRequest::findOrFail($id);
        $investment->status = 'approved';
        $investment->save();

        if ($investment->investment_type === 'turbine') {
            $turbine = Turbine::where('investment_id', $investment->id)->first();
            if ($turbine) {
                $turbine->status = 'approved';
                $turbine->save();
            }
        } elseif ($investment->investment_type === 'presenting') {
            $sponsor = PresentingSponsor::where('investment_id', $investment->id)->first();
            if ($sponsor) {
                $sponsor->status = 'approved';
                $sponsor->save();
            }
        } elseif ($investment->investment_type === 'support') {
            Support::where('investment_id', $investment->id)->update(['status' => 'approved']);
        }

        return redirect()->back()->with('status', 'Investment request approved successfully.');
    }

    public function rejectInvestment($id)
    {
        $investment = InvestmentRequest::findOrFail($id);
        $investment->status = 'blocked';
        $investment->save();

        if ($investment->investment_type === 'turbine') {
            $turbine = Turbine::where('investment_id', $investment->id)->first();
            if ($turbine) {
                $turbine->investment_id = null;
                $turbine->displayed_text = null;
                $turbine->logo_filename = null;
                $turbine->status = 'available';
                $turbine->save();
            }
        } elseif ($investment->investment_type === 'presenting') {
            $sponsor = PresentingSponsor::where('investment_id', $investment->id)->first();
            if ($sponsor) {
                $sponsor->status = 'blocked';
                $sponsor->save();
            }
        } elseif ($investment->investment_type === 'support') {
            Support::where('investment_id', $investment->id)->update(['status' => 'blocked']);
        }

        return redirect()->back()->with('status', 'Investment request rejected successfully.');
    }
}
