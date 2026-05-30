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
                return redirect()
                    ->route('marketplace.accounts')
                    ->with('error', 'Authorization code tidak ditemukan');
            }

            $company = Auth::user()?->company;

            if (!$company) {
                return redirect()
                    ->route('marketplace.accounts')
                    ->with('error', 'Company tidak ditemukan');
            }

            /*
            |--------------------------------------------------------------------------
            | 1. GET ACCESS TOKEN
            |--------------------------------------------------------------------------
            */
            $tokenResponse = Http::timeout(60)->get(
                'https://auth.tiktok-shops.com/api/v2/token/get',
                [
                    'app_key' => config('services.tiktok.app_key'),
                    'app_secret' => config('services.tiktok.app_secret'),
                    'auth_code' => $code,
                    'grant_type' => 'authorized_code',
                ]
            );

            $tokenJson = $tokenResponse->json();

            if (($tokenJson['code'] ?? -1) != 0) {

                return redirect()
                    ->route('marketplace.accounts')
                    ->with('error', $tokenJson['message'] ?? 'Gagal mendapatkan token TikTok');
            }

            $tokenData = $tokenJson['data'];

            $accessToken = $tokenData['access_token'];

            /*
            |--------------------------------------------------------------------------
            | 2. GET SHOP INFO
            |--------------------------------------------------------------------------
            */
            $path = '/seller/202309/shops';

            $timestamp = time();

            $params = [
                'app_key' => config('services.tiktok.app_key'),
                'timestamp' => $timestamp,
            ];

            $params['sign'] = $this->signTiktokRequest($path, $params);

            $shopResponse = Http::timeout(60)
                ->withHeaders([
                    'x-tts-access-token' => $accessToken,
                    'Content-Type' => 'application/json',
                ])
                ->get(
                    'https://open-api.tiktokglobalshop.com' . $path,
                    $params
                );

            $shopJson = $shopResponse->json();

            if (($shopJson['code'] ?? -1) != 0) {

                return redirect()
                    ->route('marketplace.accounts')
                    ->with('error', $shopJson['message'] ?? 'Gagal mendapatkan shop TikTok');
            }

            $shopId = data_get($shopJson, 'data.shops.0.id');
            $shopCipher = data_get($shopJson, 'data.shops.0.cipher');

            if (!$shopId) {

                return redirect()
                    ->route('marketplace.accounts')
                    ->with('error', 'Shop ID TikTok tidak ditemukan');
            }

            /*
            |--------------------------------------------------------------------------
            | 3. SIMPAN ACCOUNT
            |--------------------------------------------------------------------------
            */
            MarketplaceAccount::updateOrCreate(
                [
                    'company_id' => $company->id,
                    'platform' => 'tiktok',
                ],
                [
                    'shop_id' => $shopId,
                    'shop_cipher' => $shopCipher ?? '',
                    'shop_name' => $tokenData['seller_name'] ?? 'TikTok Shop',
                    'access_token' => $tokenData['access_token'],
                    'refresh_token' => $tokenData['refresh_token'],
                    'expired_at' => Carbon::createFromTimestamp(
                        $tokenData['access_token_expire_in']
                    ),
                ]
            );

            return redirect()
                ->route('marketplace.accounts')
                ->with('success', 'TikTok Shop berhasil terhubung.');

        } catch (\Throwable $e) {

            return redirect()
                ->route('marketplace.accounts')
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

    protected function getTiktokAuthUrl()
    {
        return rtrim(
            config(
                'services.tiktok.auth_base_url',
                'https://services.tiktokshop.com/open/authorize'
            ),
            '/'
        ) . '?' . http_build_query([
                'service_id' => config('services.tiktok.service_id'),
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

    protected function signTiktokRequest(string $path, array $params): string
    {
        $appSecret = config('services.tiktok.app_secret');

        unset($params['sign']);
        ksort($params);

        $baseString = $appSecret . $path;

        foreach ($params as $key => $value) {
            if (is_array($value) || is_object($value)) {
                $value = json_encode($value);
            }

            $baseString .= $key . $value;
        }

        $baseString .= $appSecret;

        return hash_hmac('sha256', $baseString, $appSecret);
    }
}
