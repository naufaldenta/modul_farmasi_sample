<?php

namespace App\Http\Controllers;

use App\Models\DrugCategory;
use Illuminate\Http\Request;
use Inertia\Inertia;

class DrugCategoryController extends Controller
{
    public function index()
    {
        $categories = DrugCategory::latest()->paginate(10);
        return Inertia::render('DrugCategory/Index', [
            'categories' => $categories
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:drug_categories,name',
            'description' => 'nullable|string',
        ]);

        DrugCategory::create($validated);

        return redirect()->route('drug-categories.index')->with('success', 'Kategori berhasil ditambahkan.');
    }

    public function update(Request $request, DrugCategory $drugCategory)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:drug_categories,name,' . $drugCategory->id,
            'description' => 'nullable|string',
        ]);

        $drugCategory->update($validated);

        return redirect()->route('drug-categories.index')->with('success', 'Kategori berhasil diperbarui.');
    }

    public function destroy(DrugCategory $drugCategory)
    {
        $drugCategory->delete();
        return redirect()->route('drug-categories.index')->with('success', 'Kategori berhasil dihapus.');
    }
}
