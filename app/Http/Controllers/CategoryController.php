<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Category;

class CategoryController extends Controller
{
    public function index(Request $request)
    {
        $query = Category::withCount('products');
        if ($s = $request->search) $query->where('name', 'like', "%{$s}%");
        $categories = $query->orderBy('name')->get();
        return view('categories.index', compact('categories'));
    }

    public function store(Request $request)
    {
        $data = $request->validate(['name' => 'required|unique:categories,name', 'description' => 'nullable']);
        Category::create($data);
        return redirect()->route('categories.index')->with('success', 'Kategori ditambahkan');
    }

    public function update(Request $request, Category $category)
    {
        $data = $request->validate(['name' => 'required|unique:categories,name,' . $category->id, 'description' => 'nullable']);
        $category->update($data);
        return redirect()->route('categories.index')->with('success', 'Kategori diperbarui');
    }

    public function destroy(Category $category)
    {
        if ($category->products()->count() > 0) {
            return back()->with('error', 'Kategori masih digunakan oleh ' . $category->products()->count() . ' barang. Pindahkan barang terlebih dahulu.');
        }
        $category->delete();
        return redirect()->route('categories.index')->with('success', 'Kategori dihapus');
    }
}
