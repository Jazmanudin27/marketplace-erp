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
        $accounts = MarketplaceAccount::where('company_id', Auth::user()->company_id)
            ->latest()
            ->get();

        return view('marketplace.accounts.index', compact('accounts'));
    }

    public function show(MarketplaceAccount $account)
    {
        return view('marketplace.accounts.show', compact('account'));
    }

    /**
     * Redirect ke marketplace OAuth
     */
    public function connect(Request $request)
    {
        $platform = $request->platform;

        $manager = new MarketplaceManager();

        $authUrl = $manager->driver($platform)->getAuthUrl();

        return redirect($authUrl);
    }

    /**
     * Callback OAuth Shopee / TikTok
     */
    public function callback(Request $request)
    {
        try {
            $platform = $request->platform ?? 'tiktok'; // fallback

            $manager = new MarketplaceManager();
            $driver = $manager->driver($platform);

            // ambil access token dari code
            $tokenData = $driver->getAccessToken($request->all());

            if (!$tokenData || !isset($tokenData['access_token'])) {
                return redirect()->route('marketplace.accounts')
                    ->with('error', 'Gagal mendapatkan access token');
            }

            // ambil info shop
            $shop = $driver->getShopInfo($tokenData['access_token']);

            MarketplaceAccount::updateOrCreate(
                [
                    'company_id' => Auth::user()->company_id,
                    'platform' => $platform,
                    'shop_id' => $shop['shop_id'] ?? null,
                ],
                [
                    'shop_name' => $shop['shop_name'] ?? null,
                    'access_token' => $tokenData['access_token'],
                    'refresh_token' => $tokenData['refresh_token'] ?? null,
                    'expired_at' => now()->addSeconds($tokenData['expires_in'] ?? 86400),
                ]
            );

            return redirect()->route('marketplace.accounts')
                ->with('success', 'Marketplace berhasil terhubung');

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
