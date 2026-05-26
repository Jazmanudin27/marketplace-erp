<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    /**
     * Display a listing of orders.
     */
    public function index()
    {
        return response()->json([
            'data' => [],
            'message' => 'Order list endpoint'
        ]);
    }

    /**
     * Store a newly created order.
     */
    public function store(Request $request)
    {
        return response()->json([
            'message' => 'Order created successfully'
        ], 201);
    }

    /**
     * Display the specified order.
     */
    public function show($id)
    {
        return response()->json([
            'data' => [],
            'message' => 'Order details'
        ]);
    }

    /**
     * Update the specified order.
     */
    public function update(Request $request, $id)
    {
        return response()->json([
            'message' => 'Order updated successfully'
        ]);
    }

    /**
     * Remove the specified order.
     */
    public function destroy($id)
    {
        return response()->json(null, 204);
    }
}
