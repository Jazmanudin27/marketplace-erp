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
            // DEBUG DULU (WAJIB TEST)
            // dd($request->all());

            $platform = 'tiktok';

            if (!$request->code) {
                return redirect()->route('marketplace.accounts')
                    ->with('error', 'Authorization code tidak ditemukan dari TikTok');
            }

            $manager = new MarketplaceManager();
            $driver = $manager->driver($platform);

            $tokenData = $driver->getAccessToken($request->all());

            if (!$tokenData) {
                return redirect()->route('marketplace.accounts')
                    ->with('error', 'Token gagal didapatkan');
            }

            $shop = $driver->getShopInfo($tokenData['access_token']);
            $shopId = $shop['shop_id'] ?? $shop['shop_cipher'] ?? $shop['id'] ?? null;

            MarketplaceAccount::updateOrCreate(
                [
                    'company_id' => Auth::user()->company_id,
                    'platform' => $platform,
                    'shop_id' => $shopId,
                ],
                [
                    'shop_name' => $shop['shop_name'] ?? null,
                    'shop_cipher' => $shop['shop_cipher'] ?? null,
                    'access_token' => $tokenData['access_token'],
                    'refresh_token' => $tokenData['refresh_token'] ?? null,
                    'expired_at' => now()->addSeconds($tokenData['expires_in'] ?? 86400),
                ]
            );

            return redirect()->route('marketplace.accounts')
                ->with('success', 'TikTok berhasil connect');

        } catch (\Exception $e) {
            return redirect()->route('marketplace.accounts')
                ->with('error', $e->getMessage());
        }
    }
    public function disconnect(MarketplaceAccount $account)
    {
        $account->delete();

        return back()->with('success', 'Account berhasil dihapus');
    }
}
