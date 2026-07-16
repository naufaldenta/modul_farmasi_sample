<?php

namespace App\Http\Controllers;

use App\Models\Drug;
use App\Models\DrugCategory;
use Illuminate\Http\Request;
use Inertia\Inertia;

class DrugController extends Controller
{
    public function index()
    {
        $drugs = Drug::with('category')->latest()->paginate(10);
        $categories = DrugCategory::all();
        
        return Inertia::render('Drug/Index', [
            'drugs' => $drugs,
            'categories' => $categories
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'category_id' => 'required|exists:drug_categories,id',
            'code' => 'required|string|max:50|unique:drugs,code',
            'name' => 'required|string|max:255',
            'unit' => 'required|string|max:50',
            'min_stock' => 'required|integer|min:0',
            'max_stock' => 'required|integer|min:0',
            'abc_class' => 'required|in:A,B,C',
            'description' => 'nullable|string',
        ]);

        Drug::create($validated);

        return redirect()->route('drugs.index')->with('success', 'Obat berhasil ditambahkan.');
    }

    public function update(Request $request, Drug $drug)
    {
        $validated = $request->validate([
            'category_id' => 'required|exists:drug_categories,id',
            'code' => 'required|string|max:50|unique:drugs,code,' . $drug->id,
            'name' => 'required|string|max:255',
            'unit' => 'required|string|max:50',
            'min_stock' => 'required|integer|min:0',
            'max_stock' => 'required|integer|min:0',
            'abc_class' => 'required|in:A,B,C',
            'description' => 'nullable|string',
        ]);

        $drug->update($validated);

        return redirect()->route('drugs.index')->with('success', 'Obat berhasil diperbarui.');
    }

    public function destroy(Drug $drug)
    {
        $drug->delete();
        return redirect()->route('drugs.index')->with('success', 'Obat berhasil dihapus.');
    }
}
