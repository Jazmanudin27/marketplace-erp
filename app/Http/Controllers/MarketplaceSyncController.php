<?php

namespace App\Http\Controllers;

use App\Models\MarketplaceAccount;
use App\Services\Marketplace\MarketplaceManager;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class MarketplaceSyncController extends Controller
{
    protected $marketplaceManager;

    public function __construct(MarketplaceManager $marketplaceManager)
    {
        $this->marketplaceManager = $marketplaceManager;
    }

    public function syncProducts($id)
    {
        try {

            $account = MarketplaceAccount::findOrFail($id);

            /*
            |--------------------------------------------------------------------------
            | Ambil Driver Marketplace
            |--------------------------------------------------------------------------
            */

            $service = $this->marketplaceManager->driver(
                $account->platform
            );

            /*
            |--------------------------------------------------------------------------
            | Sync / Ambil Produk
            |--------------------------------------------------------------------------
            */

            $products = $service->getProducts($account);

            return response()->json([
                'success' => true,
                'message' => 'Produk berhasil disinkronkan',
                'data' => $products,
            ]);

        } catch (\Exception $e) {

            Log::error('Sync Product Error', [
                'message' => $e->getMessage(),
                'line' => $e->getLine(),
                'file' => $e->getFile(),
            ]);

            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'line' => $e->getLine(),
            ], 500);
        }
    }
}
