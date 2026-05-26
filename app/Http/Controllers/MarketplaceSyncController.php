<?php

namespace App\Http\Controllers;

use App\Models\MarketplaceAccount;
use App\Services\Marketplace\MarketplaceManager;

class MarketplaceSyncController extends Controller
{
    public function syncProducts($id)
    {
        $account = MarketplaceAccount::findOrFail($id);



        dd($account);
    }
}
