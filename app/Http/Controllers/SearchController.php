<?php

namespace App\Http\Controllers;

use App\Models\products as Product;
use App\Models\Subcategory;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    /**
     * Live search suggestions for the header search bar.
     * Returns a small set of matching subcategories and products.
     *
     * GET /search/suggest?q=t shirt
     */
    public function suggest(Request $request)
    {
        $q = trim((string) $request->query('q', ''));

        if (mb_strlen($q) < 2) {
            return response()->json([
                'subcategories' => [],
                'products'      => [],
            ]);
        }

        $subcategories = Subcategory::with('category')
            ->where('name', 'like', "%{$q}%")
            ->orderBy('name')
            ->limit(4)
            ->get()
            ->map(function ($sub) {
                return [
                    'id'            => $sub->id,
                    'name'          => $sub->name,
                    'category_name' => $sub->category->name ?? null,
                    'url'           => url('products') . '?subcategory_id=' . $sub->id,
                ];
            });

        $products = Product::where('name', 'like', "%{$q}%")
            ->orderBy('name')
            ->limit(6)
            ->get()
            ->map(function ($p) {
                return [
                    'id'    => $p->id,
                    'name'  => $p->name,
                    'price' => $p->price,
                    'image' => $p->image ? asset('images/' . $p->image) : null,
                    'url'   => url('/product/' . $p->id),
                ];
            });

        return response()->json([
            'subcategories' => $subcategories,
            'products'      => $products,
        ]);
    }
}