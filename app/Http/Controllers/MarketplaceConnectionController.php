<?php

namespace App\Http\Controllers;

use App\Models\MarketplaceAccount;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
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
            case 'tiktok':
                return redirect($this->getTiktokAuthUrl());

            case 'lazada':
                return redirect($this->getLazadaAuthUrl());
        }
    }

   public function callback(Request $request)
{
    try {

        $code = $request->query('code');

        if (!$code || $code === 'null') {
            dd('Authorization code tidak ditemukan');
        }

        $company = Auth::user()?->company;

        if (!$company) {
            dd('Company tidak ditemukan');
        }

        /*
        |--------------------------------------------------------------------------
        | 1. GET TOKEN
        |--------------------------------------------------------------------------
        */
        $tokenResponse = Http::timeout(60)->get(
            'https://auth.tiktok-shops.com/api/v2/token/get',
            [
                'app_key'     => config('services.tiktok.app_key'),
                'app_secret'  => config('services.tiktok.app_secret'),
                'auth_code'   => $code,
                'grant_type'  => 'authorized_code',
            ]
        );

        $tokenJson = $tokenResponse->json();

        if (($tokenJson['code'] ?? -1) != 0) {
            dd([
                'step' => 'GET TOKEN FAILED',
                'response' => $tokenJson,
            ]);
        }

        $tokenData = $tokenJson['data'];

        $accessToken = $tokenData['access_token'];

        /*
        |--------------------------------------------------------------------------
        | 2. GET AUTHORIZED SHOPS
        |--------------------------------------------------------------------------
        */
        $timestamp = time();

        $appKey    = config('services.tiktok.app_key');
        $appSecret = config('services.tiktok.app_secret');

        $path = '/authorization/202309/shops';

        /*
        |--------------------------------------------------------------------------
        | SIGN (DEBUG)
        |--------------------------------------------------------------------------
        */
        $signString = $appKey . $path . $timestamp;

        $sign = hash_hmac(
            'sha256',
            $signString,
            $appSecret
        );

        $shopResponse = Http::timeout(60)
            ->withHeaders([
                'Content-Type' => 'application/json',
                'x-tts-access-token' => $accessToken,
            ])
            ->get(
                'https://open-api.tiktokglobalshop.com/authorization/202309/shops',
                [
                    'app_key'   => $appKey,
                    'timestamp' => $timestamp,
                    'sign'      => $sign,
                ]
            );

        $shopJson = $shopResponse->json();

        dd([
            'oauth_response' => $tokenJson,
            'shops_response' => $shopJson,
        ]);

    } catch (\Exception $e) {

        dd([
            'message' => $e->getMessage(),
            'line'    => $e->getLine(),
            'file'    => $e->getFile(),
        ]);
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

    protected function getTiktokAuthUrl()
    {
        $serviceId = config('services.tiktok.service_id');

        if ($serviceId) {
            $baseUrl = rtrim(
                config('services.tiktok.auth_base_url', 'https://services.tiktokshop.com/open/authorize'),
                '/'
            );

            return $baseUrl . '?' . http_build_query([
                'service_id' => $serviceId,
                'state' => csrf_token(),
            ]);
        }

        $appKey = config('services.tiktok.app_key');
        $redirectUri = config('services.tiktok.redirect_url');

        return 'https://auth.tiktok-shops.com/oauth/authorize?' . http_build_query([
            'app_key' => $appKey,
            'response_type' => 'code',
            'redirect_uri' => $redirectUri,
            'scope' => 'shop.basic_info,order.read',
            'state' => csrf_token(),
        ]);
    }

    // protected function getTiktokAuthUrl()
    // {
    //     $app_key = config('services.tiktok.app_key');
    //     // $redirect_uri = route('marketplace.callback');
    //     $redirect_uri = url('/callback/tiktok');

    //     return "https://auth.tiktok-shops.com/oauth/authorize?" . http_build_query([
    //         'app_key' => $app_key,
    //         'response_type' => 'code',
    //         'redirect_uri' => $redirect_uri,
    //         'scope' => 'shop.basic_info,order.read',
    //         'state' => csrf_token(),
    //     ]);
    // }

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
