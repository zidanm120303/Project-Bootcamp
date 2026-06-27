<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class CategoryController extends Controller
{
    public function index()
    {
        return view('admin.categories', ['categories' => Category::withCount('products')->orderBy('name')->get()]);
    }

    public function store()
    {
        $data = request()->validate(['name' => ['required', 'string', 'max:120', 'unique:categories,name'], 'icon' => ['nullable', 'string', 'max:100']]);
        Category::create($data + ['slug' => Str::slug($data['name']), 'status' => 'active']);

        return back()->with('success', 'Kategori berhasil ditambahkan.');
    }

    public function update(Category $category)
    {
        $data = request()->validate([
            'name' => ['required', 'string', 'max:120', Rule::unique('categories')->ignore($category)],
            'status' => ['required', 'in:active,inactive'],
        ]);
        $category->update($data + ['slug' => Str::slug($data['name'])]);

        return back()->with('success', 'Kategori berhasil diperbarui.');
    }

    public function destroy(Category $category)
    {
        abort_if($category->products()->exists(), 422, 'Kategori masih digunakan produk.');
        $category->delete();

        return back()->with('success', 'Kategori dihapus.');
    }
}
