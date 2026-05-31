<?php

namespace App\Http\Controllers;

use App\Models\MarketplaceAccount;
use App\Services\Marketplace\MarketplaceManager;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;

class MarketplaceConnectionController extends Controller
{

    public function index()
    {
        $company = Auth::user()->company;
        $accounts = $company->marketplaceAccounts()->get();

        return view('marketplace.accounts', compact('accounts', 'company'));
    }


    public function show(MarketplaceAccount $account)
    {
        $this->authorize('view', $account);

        return view('marketplace.account-detail', compact('account'));
    }


    /**
     * Redirect ke marketplace OAuth
     */
    public function connect(Request $request)
    {
        try {
            $request->validate([
                'platform' => 'required|in:shopee,tiktok'
            ]);

            $platform = $request->platform;

            $manager = new MarketplaceManager();

            $driver = $manager->driver($platform);

            $authUrl = $driver->getAuthUrl();

            return redirect()->away($authUrl);

        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Callback OAuth Shopee / TikTok
     */
    public function callback(Request $request)
{
    try {

        $manager = new MarketplaceManager();
        $driver = $manager->driver('tiktok');

        dd($driver);

    } catch (\Throwable $e) {

        dd($e->getMessage());
    }
}
    public function disconnect(MarketplaceAccount $account)
    {
        $account->delete();

        return back()->with('success', 'Account berhasil dihapus');
    }
}
