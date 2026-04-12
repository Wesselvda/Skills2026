<?php

namespace App\Http\Controllers;

use App\Models\Company;
use Illuminate\Http\Request;

class CompanyController extends Controller
{
    public function index()
    {
        $companies = Company::where('is_deactivated', false)
            ->orderBy('name')
            ->get();

        return view('admin.companies.index', [
            'companies' => $companies,
            'title' => 'Active companies',
        ]);
    }

    public function deactivated()
    {
        $companies = Company::where('is_deactivated', true)
            ->orderBy('name')
            ->get();

        return view('admin.companies.index', [
            'companies' => $companies,
            'title' => 'Deactivated companies',
        ]);
    }

    public function create()
    {
        return view('admin.companies.form', [
            'company' => new Company(),
            'isEdit' => false,
        ]);
    }

    public function store(Request $request)
    {
        $company = Company::create($this->validatedData($request));

        return redirect()->route('companies.show', $company)->with('status', 'Company created.');
    }

    public function show(Company $company)
    {
        $company->load('products');

        return view('admin.companies.show', [
            'company' => $company,
        ]);
    }

    public function edit(Company $company)
    {
        return view('admin.companies.form', [
            'company' => $company,
            'isEdit' => true,
        ]);
    }

    public function update(Request $request, Company $company)
    {
        $company->update($this->validatedData($request));

        return redirect()->route('companies.show', $company)->with('status', 'Company updated.');
    }

    public function deactivate(Company $company)
    {
        $company->update(['is_deactivated' => true]);
        $company->products()->update(['is_hidden' => true]);

        return redirect()->route('companies.show', $company)->with('status', 'Company deactivated. All products are now hidden.');
    }

    private function validatedData(Request $request): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'address' => ['required', 'string', 'max:255'],
            'telephone' => ['required', 'string', 'max:50'],
            'email' => ['required', 'email', 'max:255'],
            'owner_name' => ['required', 'string', 'max:255'],
            'owner_mobile' => ['required', 'string', 'max:50'],
            'owner_email' => ['required', 'email', 'max:255'],
            'contact_name' => ['required', 'string', 'max:255'],
            'contact_mobile' => ['required', 'string', 'max:50'],
            'contact_email' => ['required', 'email', 'max:255'],
        ]);
    }
}
