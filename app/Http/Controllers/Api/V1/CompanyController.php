<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class CompanyController extends Controller
{
    /**
     * Display a listing of companies.
     */
    public function index()
    {
        return response()->json([
            'data' => [],
            'message' => 'Company list endpoint'
        ]);
    }

    /**
     * Store a newly created company.
     */
    public function store(Request $request)
    {
        return response()->json([
            'message' => 'Company created successfully'
        ], 201);
    }

    /**
     * Display the specified company.
     */
    public function show($id)
    {
        return response()->json([
            'data' => [],
            'message' => 'Company details'
        ]);
    }

    /**
     * Update the specified company.
     */
    public function update(Request $request, $id)
    {
        return response()->json([
            'message' => 'Company updated successfully'
        ]);
    }

    /**
     * Remove the specified company.
     */
    public function destroy($id)
    {
        return response()->json(null, 204);
    }
}
