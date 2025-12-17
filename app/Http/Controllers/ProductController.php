<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{

    public function index()
    {
        $products = Product::all();

        return response()->json([
            "status" => "success",
            "data" => $products,
            "message" => "Products retrieved successfully"
        ]);
    }

    public function store(Request $request)
    {
        return response()->json([
            "status" => "success",
            "data" => Product::create($request),
            "message" => "Product created successfully"
        ], 201);
    }


    public function show(Product $id)
    {
        return response()->json([
            "status" => "success",
            "data" => $id,
            "message" => "Product retrieved successfully"
        ]);
    }

    public function update(Request $storeProducto, Product $id)
    {
        $product = $id;
        $product->update($storeProducto);
        return response()->json([
            "status" => "success",
            "data" => $product,
            "message" => "Product updated successfully"
        ]);
    }

    public function destroy(string $id)
    {
        $product = Product::find($id);
        $product->delete();

        return response()->json([
            "status" => "success",
            "message" => "Product deleted successfully"
        ]);
    }
}
