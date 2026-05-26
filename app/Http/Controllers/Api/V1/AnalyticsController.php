<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class AnalyticsController extends Controller
{
    /**
     * Dashboard analytics.
     */
    public function dashboard()
    {
        return response()->json([
            'data' => [
                'total_orders' => 0,
                'total_revenue' => 0,
                'total_products' => 0,
                'active_marketplaces' => 0,
            ]
        ]);
    }

    /**
     * Sales analytics.
     */
    public function sales()
    {
        return response()->json([
            'data' => []
        ]);
    }

    /**
     * Inventory analytics.
     */
    public function inventory()
    {
        return response()->json([
            'data' => []
        ]);
    }
}
