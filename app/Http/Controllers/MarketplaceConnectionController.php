<?php

namespace App\Http\Controllers;

use App\Models\MarketplaceAccount;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;

class MarketplaceConnectionController extends Controller
{
    use AuthorizesRequests;

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

    public function connect(Request $request)
    {
        $validated = $request->validate([
            'platform' => 'required|in:shopee,tokopedia,tiktok,lazada',
        ]);

        $platform = $validated['platform'];

        // SIMPAN PLATFORM DI SESSION
        session(['oauth_platform' => $platform]);

        switch ($platform) {
            case 'shopee':
                return redirect($this->getShopeeAuthUrl());

            case 'tokopedia':
                return redirect($this->getTokopediaAuthUrl());

            case 'tiktok':
                return redirect($this->getTiktokAuthUrl());

            case 'lazada':
                return redirect($this->getLazadaAuthUrl());
        }
    }

    public function callback(Request $request)
    {
        try {

            $code = $request->code;

            if (!$code) {
                return redirect()
                    ->route('marketplace.connections')
                    ->with('error', 'Authorization code tidak ditemukan');
            }

            $tokenResponse = Http::get(
                'https://auth.tiktok-shops.com/api/v2/token/get',
                [
                    'app_key' => env('TIKTOK_APP_KEY'),
                    'app_secret' => env('TIKTOK_APP_SECRET'),
                    'auth_code' => $code,
                    'grant_type' => 'authorized_code',
                ]
            );

            $response = $tokenResponse->json();

            // cek gagal
            if (($response['code'] ?? -1) != 0) {

                return redirect()
                    ->route('marketplace.connections')
                    ->with('error', $response['message'] ?? 'Gagal koneksi TikTok');
            }

            $data = $response['data'];

            MarketplaceAccount::updateOrCreate(
                [
                    'platform' => 'tiktok',
                    'open_id' => $data['open_id'],
                ],
                [
                    'shop_name' => $data['seller_name'] ?? null,
                    'shop_region' => $data['seller_base_region'] ?? null,

                    'access_token' => $data['access_token'],
                    'refresh_token' => $data['refresh_token'],

                    'token_expires_at' => date(
                        'Y-m-d H:i:s',
                        $data['access_token_expire_in']
                    ),

                    'refresh_token_expires_at' => date(
                        'Y-m-d H:i:s',
                        $data['refresh_token_expire_in']
                    ),

                    'status' => 'connected',
                ]
            );

            return redirect()
                ->route('marketplace.connections')
                ->with('success', 'TikTok Shop berhasil terhubung');

        } catch (\Exception $e) {

            return redirect()
                ->route('marketplace.connections')
                ->with('error', $e->getMessage());
        }
    }

    public function disconnect(MarketplaceAccount $account)
    {
        $this->authorize('delete', $account);

        $platform = $account->platform;
        $account->delete();

        return back()->with('success', ucfirst($platform) . ' berhasil terputus.');
    }

    protected function getShopeeAuthUrl()
    {
        $partner_id = config('services.shopee.partner_id');
        $redirect_uri = route('marketplace.callback');

        return "https://partner.shopeemobile.com/api/v2/oauth/authorize?" .
            http_build_query([
                'client_id' => $partner_id,
                'response_type' => 'code',
                'redirect_uri' => $redirect_uri,
                'state' => csrf_token(),
            ]);
    }

    protected function getTokopediaAuthUrl()
    {
        $client_id = config('services.tokopedia.client_id');
        $redirect_uri = route('marketplace.callback');

        return "https://accounts.tokopedia.com/authorize?" . http_build_query([
            'client_id' => $client_id,
            'redirect_uri' => $redirect_uri,
            'response_type' => 'code',
            'state' => csrf_token(),
        ]);
    }

    protected function getTiktokAuthUrl()
    {
        $app_key = config('services.tiktok.app_key');
        // $redirect_uri = route('marketplace.callback');
        $redirect_uri = url('/callback/tiktok');

        return "https://auth.tiktok-shops.com/oauth/authorize?" . http_build_query([
            'app_key' => $app_key,
            'response_type' => 'code',
            'redirect_uri' => $redirect_uri,
            'scope' => 'shop.basic_info,order.read',
            'state' => csrf_token(),
        ]);
    }

    protected function getLazadaAuthUrl()
    {
        $client_id = config('services.lazada.client_id');
        $redirect_uri = route('marketplace.callback');

        return "https://auth.lazada.com/oauth/authorize?" .
            http_build_query([
                'client_id' => $client_id,
                'response_type' => 'code',
                'redirect_uri' => $redirect_uri,
                'state' => csrf_token(),
            ]);
    }

    protected function exchangeCodeForTokens($platform, $code, $shop_id = null)
    {
        // This should be implemented based on each platform's API
        // For now, return placeholder
        return [
            'access_token' => 'dummy_token_' . time(),
            'refresh_token' => 'dummy_refresh_' . time(),
            'shop_name' => 'Shop Name',
            'expired_at' => now()->addDays(30),
        ];
    }
}
