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

        $platform = 'tiktok';

        $manager = new MarketplaceManager();
        $driver = $manager->driver($platform);

        $response = Http::get(
            'https://auth.tiktok-shops.com/api/v2/token/get',
            [
                'app_key' => config('services.tiktok.app_key'),
                'app_secret' => config('services.tiktok.app_secret'),
                'auth_code' => $request->code,
                'grant_type' => 'authorized_code',
            ]
        );

        $data = $response->json();

        if (($data['code'] ?? 1) !== 0) {
            dd($data);
        }

        $tokenData = $data['data'];
        dd([
            'status' => $response->status(),
            'json' => $response->json(),
            'body' => $response->body(),
            'tokenData' => $tokenData,
        ]);
        /*
        |--------------------------------------------------------------------------
        | Ambil Data Shop
        |--------------------------------------------------------------------------
        */

        $shop = $shopInfo['shops'][0] ?? null;


        $shopId = $shop['id'];

        /*
        |--------------------------------------------------------------------------
        | Simpan / Update Marketplace Account
        |--------------------------------------------------------------------------
        */

        // MarketplaceAccount::updateOrCreate(
        //     [
        //         'company_id' => Auth::user()->company_id,
        //         'platform' => 'tiktok',
        //         'shop_id' => $shopId,
        //     ],
        //     [
        //         'shop_name' => $tokenData['seller_name'] ?? 'TikTok Shop',
        //         'shop_cipher' => $shopId, // API terbaru tidak mengembalikan shop_cipher
        //         'access_token' => $tokenData['access_token'],
        //         'refresh_token' => $tokenData['refresh_token'] ?? null,
        //         'expired_at' => $tokenData['access_token_expire_in'] ?? null,
        //     ]
        // );

        // return redirect()
        //     ->route('marketplace.accounts')
        //     ->with('success', 'TikTok Shop berhasil terhubung');


    }
    public function disconnect(MarketplaceAccount $account)
    {
        $account->delete();

        return back()->with('success', 'Account berhasil dihapus');
    }
}
