<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    /**
     * Login user.
     */
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required|min:8',
        ]);

        return response()->json([
            'message' => 'Login endpoint - Implement authentication logic',
        ]);
    }

    /**
     * Register user.
     */
    public function register(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:8|confirmed',
            'company_name' => 'required|string|max:255',
        ]);

        return response()->json([
            'message' => 'Register endpoint - Implement registration logic',
        ], 201);
    }
}
