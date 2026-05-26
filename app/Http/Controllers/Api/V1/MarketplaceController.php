<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class MarketplaceController extends Controller
{
    /**
     * Display a listing of marketplaces.
     */
    public function index()
    {
        return response()->json([
            'data' => [],
            'message' => 'Marketplace list endpoint'
        ]);
    }

    /**
     * Store a newly created marketplace.
     */
    public function store(Request $request)
    {
        return response()->json([
            'message' => 'Marketplace created successfully'
        ], 201);
    }

    /**
     * Display the specified marketplace.
     */
    public function show($id)
    {
        return response()->json([
            'data' => [],
            'message' => 'Marketplace details'
        ]);
    }

    /**
     * Update the specified marketplace.
     */
    public function update(Request $request, $id)
    {
        return response()->json([
            'message' => 'Marketplace updated successfully'
        ]);
    }

    /**
     * Remove the specified marketplace.
     */
    public function destroy($id)
    {
        return response()->json(null, 204);
    }
}
