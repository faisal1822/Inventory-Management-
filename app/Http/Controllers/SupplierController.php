<?php

namespace App\Http\Controllers;

use App\Http\Requests\SupplierRequest;
use App\Models\Supplier;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SupplierController extends Controller
{
    public function index(Request $request): View
    {
        $search = $request->query('search');

        $suppliers = Supplier::withCount('purchases')
            ->when($search, function ($query, $search) {
                $query->where('name', 'like', "%{$search}%")
                      ->orWhere('company_name', 'like', "%{$search}%")
                      ->orWhere('phone', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%");
            })
            ->latest('id')
            ->paginate(10)
            ->withQueryString();

        return view('suppliers.index', compact('suppliers', 'search'));
    }

    public function create(): View
    {
        return view('suppliers.create');
    }

    public function store(SupplierRequest $request): RedirectResponse
    {
        Supplier::create([
            'name' => $request->name,
            'company_name' => $request->company_name,
            'phone' => $request->phone,
            'email' => $request->email,
            'address' => $request->address,
            'notes' => $request->notes,
            'is_active' => $request->boolean('is_active', true),
        ]);

        return redirect()->route('suppliers.index')
            ->with('success', 'Supplier added successfully.');
    }

    public function show(Supplier $supplier): View
    {
        $supplier->load(['purchases.items.product', 'purchases.user']);

        return view('suppliers.show', compact('supplier'));
    }

    public function edit(Supplier $supplier): View
    {
        return view('suppliers.edit', compact('supplier'));
    }

    public function update(SupplierRequest $request, Supplier $supplier): RedirectResponse
    {
        $supplier->update([
            'name' => $request->name,
            'company_name' => $request->company_name,
            'phone' => $request->phone,
            'email' => $request->email,
            'address' => $request->address,
            'notes' => $request->notes,
            'is_active' => $request->boolean('is_active', true),
        ]);

        return redirect()->route('suppliers.index')
            ->with('success', 'Supplier updated successfully.');
    }

    public function destroy(Supplier $supplier): RedirectResponse
    {
        // Safe check: prevent deletion of suppliers with transactions
        if ($supplier->purchases()->count() > 0) {
            return back()->with('error', "Cannot delete supplier '{$supplier->name}' because they have {$supplier->purchases()->count()} recorded purchase transaction(s). You may mark them as inactive instead.");
        }

        $supplier->delete();

        return redirect()->route('suppliers.index')
            ->with('success', 'Supplier deleted successfully.');
    }
}
