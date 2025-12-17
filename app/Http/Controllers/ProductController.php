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

        $validatedData = $request->validate([
            'name' => 'required|string|max:20',
            'price' => 'required|numeric',
            'description' => 'nullable|string',
        ]);

        $product = Product::create($validatedData);

        return response()->json([
            "status" => "success",
            "data" => $product,
            "message" => "Product created successfully"
        ], 201);
    }
    public function show(string $id)
    {

        return response()->json([
            "status" => "success",
            "data" => Product::find($id),
            "message" => "Product retrieved successfully"
        ]);
    }

    public function update(Request $request, string $id)
    {
        $product = Product::findOrFail($id);
        $validatedData = $request->validate([
            'name' => 'sometimes|required|string|max:20',
            'price' => 'sometimes|required|numeric',
            'description' => 'nullable|string',
        ]);
        $product->update($validatedData);

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
