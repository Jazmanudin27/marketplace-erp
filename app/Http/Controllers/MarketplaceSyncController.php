<?php

namespace App\Http\Controllers;

use App\Models\MarketplaceAccount;
use App\Services\Marketplace\MarketplaceManager;

class MarketplaceSyncController extends Controller
{
    public function syncProducts($id)
    {
        try {

            $account = MarketplaceAccount::findOrFail($id);

            $service = MarketplaceManager::driver(
                $account->platform
            );

            $products = $service->getProducts($account);

            dd($products);

        } catch (\Exception $e) {

            dd([
                'message' => $e->getMessage(),
                'line' => $e->getLine(),
                'file' => $e->getFile(),
            ]);
        }
    }
}
