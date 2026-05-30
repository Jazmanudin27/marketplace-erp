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
            $service = MarketplaceManager::driver($account->platform);
            dd($service);

            // $result = $service->getProducts($account);

            // return response()->json($result);

        } catch (\Throwable $e) {
            dd([
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);
        }
    }
}
