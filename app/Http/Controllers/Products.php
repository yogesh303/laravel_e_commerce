<?php

namespace App\Http\Controllers;

use App\Models\CartItem;
use App\Models\products as ModelsProducts;


use Illuminate\Http\Request;

class Products extends Controller
{
    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
        $imageName = '';

        if ($request->hasFile('image')) {
            $imageName = time().'.'.$request->image->extension();
            $request->image->move(public_path('images'), $imageName);
        }
        $products = ModelsProducts::create([
            'name'=> $request->name,
            'price'=> $request->price,
            'description'=> $request->description,
            'stock'=> $request->stock,
            'image' => $imageName,
        ]);
        $products = ModelsProducts::where('id',$request->id)->update([
            'name' => $request->name,
        ]);
        if($products){
            return redirect('product_list');
        }
    }

    /**
     * Display the specified resource.
     */
    public function show()
    {
        //
        $products = ModelsProducts::all();
        return view('productlist',['products'=>$products]);
        
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
        $products = ModelsProducts::find($id);
        return view('productform', ['products' => $products]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request)
    {
        //
        $id = $request->id;
        $products = ModelsProducts::find($id);

        $products->name = $request->name;
        $products->price = $request->price;
        $products->description = $request->description;
        $products->stock = $request->stock;

        if ($request->hasFile('image')) {

            if ($products->image && file_exists(public_path('images/' . $products->image))) {
                unlink(public_path('images/' . $products->image));
            }

            $imageName = time().'.'.$request->image->extension();
            $request->image->move(public_path('images'), $imageName);

            $products->image = $imageName;
        }

        $products->save();
        if($products){
            return redirect('product_list');
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function delete($id)
    {
        //
        $products = ModelsProducts::find($id);
        $products->delete();
        if($products){
            return redirect('product_list');
        }
    }
    public function products_card(){
        $products = ModelsProducts::all();
        return view('productcards',['products'=>$products]);
    }
}
