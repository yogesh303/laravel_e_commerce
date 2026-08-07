<?php

namespace App\Http\Controllers;

use App\Models\products as ModelsProducts;
use App\Models\ProductImage;
use App\Models\ProductOption;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class Products extends Controller
{
    public function store(Request $request)
    {
        DB::beginTransaction();

        try {
            $imageName = '';

            if ($request->hasFile('image')) {
                $imageName = time() . '.' . $request->image->extension();
                $request->image->move(public_path('images'), $imageName);
            }

            $product = ModelsProducts::create([
                'name'        => $request->name,
                'price'       => $request->price,
                'description' => $request->description,
                'stock'       => $request->stock,
                'image'       => $imageName,
            ]);

            $this->saveGalleryImages($request, $product);
            $this->saveOptions($request, $product);

            DB::commit();

            return redirect('product_list');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => $e->getMessage()])->withInput();
        }
    }

    public function show()
    {
        $products = ModelsProducts::with(['images', 'options'])->get();
        return view('productlist', ['products' => $products]);
    }

    public function edit(string $id)
    {
        $products = ModelsProducts::with(['images', 'options'])->find($id);
        return view('productform', ['products' => $products]);
    }

    public function update(Request $request)
    {
        DB::beginTransaction();

        try {
            $id = $request->id;
            $product = ModelsProducts::find($id);

            $product->name        = $request->name;
            $product->price       = $request->price;
            $product->description = $request->description;
            $product->stock       = $request->stock;

            // Replace main image
            if ($request->hasFile('image')) {
                if ($product->image && file_exists(public_path('images/' . $product->image))) {
                    unlink(public_path('images/' . $product->image));
                }

                $imageName = time() . '.' . $request->image->extension();
                $request->image->move(public_path('images'), $imageName);
                $product->image = $imageName;
            }

            $product->save();

            // Remove gallery images the user deleted in the form
            if ($request->filled('remove_images')) {
                $toRemove = ProductImage::where('product_id', $product->id)
                    ->whereIn('id', $request->remove_images)
                    ->get();

                foreach ($toRemove as $img) {
                    if (file_exists(public_path('images/' . $img->image))) {
                        unlink(public_path('images/' . $img->image));
                    }
                    $img->delete();
                }
            }

            // Update "customizable" checkbox state on remaining existing images
            if ($request->filled('existing_images')) {
                foreach ($request->existing_images as $imgId) {
                    $isCustomizable = $request->has("existing_customizable_{$imgId}");
                    ProductImage::where('id', $imgId)->update(['is_customizable' => $isCustomizable]);
                }
            }

            // Add any newly uploaded gallery images
            $this->saveGalleryImages($request, $product);

            // Replace dynamic options (simplest reliable approach: wipe + recreate)
            ProductOption::where('product_id', $product->id)->delete();
            $this->saveOptions($request, $product);

            DB::commit();

            return redirect('product_list');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => $e->getMessage()])->withInput();
        }
    }

    public function delete($id)
    {
        $product = ModelsProducts::with('images')->find($id);

        if (!$product) {
            return redirect('product_list');
        }

        // Delete main image file
        if ($product->image && file_exists(public_path('images/' . $product->image))) {
            unlink(public_path('images/' . $product->image));
        }

        // Delete every gallery image file
        foreach ($product->images as $img) {
            if (file_exists(public_path('images/' . $img->image))) {
                unlink(public_path('images/' . $img->image));
            }
        }

        // product_images + product_options rows auto-delete via onDelete('cascade')
        $product->delete();

        return redirect('product_list');
    }

    public function products_card()
    {
        $products = ModelsProducts::with(['images', 'options'])->get();
        return view('productcards', ['products' => $products]);
    }

    /**
     * Handle multiple gallery image uploads + their "customizable" checkboxes.
     * Expects: images[] (files) and customizable[] (checkbox per index, e.g. customizable[0], customizable[2])
     */
    private function saveGalleryImages(Request $request, $product)
    {
        if (!$request->hasFile('images')) {
            return;
        }

        foreach ($request->file('images') as $index => $file) {
            if (!$file) {
                continue;
            }

            $imageName = time() . '_' . $index . '_' . uniqid() . '.' . $file->extension();
            $file->move(public_path('images'), $imageName);

            ProductImage::create([
                'product_id'      => $product->id,
                'image'           => $imageName,
                'is_customizable' => $request->has("customizable.$index"),
            ]);
        }
    }

    /**
     * Handle dynamic option fields.
     * Expects: options[][name] = "Size", options[][values] = "S,M,L,XL"
     */
    private function saveOptions(Request $request, $product)
    {
        if (!$request->filled('options')) {
            return;
        }

        foreach ($request->options as $option) {
            if (empty($option['name']) || empty($option['values'])) {
                continue;
            }

            ProductOption::create([
                'product_id' => $product->id,
                'name'       => $option['name'],
                'values'     => $option['values'],
            ]);
        }
    }
}