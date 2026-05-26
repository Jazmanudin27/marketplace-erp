<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    /**
     * Display a listing of products.
     */
    public function index()
    {
        return response()->json([
            'data' => [],
            'message' => 'Product list endpoint'
        ]);
    }

    /**
     * Store a newly created product.
     */
    public function store(Request $request)
    {
        return response()->json([
            'message' => 'Product created successfully'
        ], 201);
    }

    /**
     * Display the specified product.
     */
    public function show($id)
    {
        return response()->json([
            'data' => [],
            'message' => 'Product details'
        ]);
    }

    /**
     * Update the specified product.
     */
    public function update(Request $request, $id)
    {
        return response()->json([
            'message' => 'Product updated successfully'
        ]);
    }

    /**
     * Remove the specified product.
     */
    public function destroy($id)
    {
        return response()->json(null, 204);
    }
}
