<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class WebhookController extends Controller
{
    /**
     * Handle marketplace webhook.
     */
    public function handle($account, Request $request)
    {
        return response()->json([
            'message' => 'Webhook received and queued for processing'
        ]);
    }
}
