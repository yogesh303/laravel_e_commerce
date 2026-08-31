<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Subcategory;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function index()
    {
        $categories = Category::with('subcategories')->get();
        return view('categories', ['categories' => $categories]);
    }

    public function store(Request $request)
    {
        $request->validate(['name' => 'required|string|max:255']);

        Category::create(['name' => $request->name]);

        return back()->with('success', 'Category added.');
    }

    public function destroy($id)
    {
        Category::findOrFail($id)->delete(); // subcategories cascade-delete
        return back()->with('success', 'Category deleted.');
    }

    public function storeSubcategory(Request $request)
    {
        $request->validate([
            'category_id' => 'required|exists:categories,id',
            'name'        => 'required|string|max:255',
        ]);

        Subcategory::create([
            'category_id' => $request->category_id,
            'name'        => $request->name,
        ]);

        return back()->with('success', 'Subcategory added.');
    }

    public function destroySubcategory($id)
    {
        Subcategory::findOrFail($id)->delete();
        return back()->with('success', 'Subcategory deleted.');
    }

    // AJAX endpoint used by the product form
    public function getSubcategories($category_id)
    {
        $subcategories = Subcategory::where('category_id', $category_id)->get();
        return response()->json($subcategories);
    }
    public function update(Request $request, $id)
    {
        $request->validate(['name' => 'required|string|max:255']);

        $category = Category::findOrFail($id);
        $category->update(['name' => $request->name]);

        return back()->with('success', 'Category updated.');
    }
    public function updateSubcategory(Request $request, $id)
    {
        $request->validate(['name' => 'required|string|max:255']);

        $subcategory = Subcategory::findOrFail($id);
        $subcategory->update(['name' => $request->name]);

        return back()->with('success', 'Subcategory updated.');
    }
}