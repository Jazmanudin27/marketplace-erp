<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class MarketplaceConnectionException extends Exception
{
    /**
     * Report the exception.
     */
    public function report(): void
    {
        Log::error('Marketplace Connection Error: ' . $this->message);
    }

    /**
     * Render the exception into an HTTP response.
     */
    public function render(Request $request): JsonResponse
    {
        return response()->json([
            'error' => 'Marketplace Connection Failed',
            'message' => $this->message,
        ], 500);
    }
}
