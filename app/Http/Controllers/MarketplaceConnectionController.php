<?php

namespace App\Http\Controllers;

use App\Models\MarketplaceAccount;
use Illuminate\Support\Carbon;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class MarketplaceConnectionController extends Controller
{
    use AuthorizesRequests;

    public function index()
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $companyId = session('company_id') ?? $user->company_id;
        $company = $user->companies()->where('companies.id', $companyId)->first() ?? $user->company;
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
        $companyId = Auth::user()->company_id ?? session('company_id');

        // SIMPAN PLATFORM DI SESSION
        session([
            'oauth_platform' => $platform,
            'oauth_company_id' => $companyId,
        ]);

        switch ($platform) {
            case 'shopee':
                return redirect($this->getShopeeAuthUrl());

            case 'tiktok':
                return redirect($this->getTiktokAuthUrl($companyId));

            case 'lazada':
                return redirect($this->getLazadaAuthUrl());
        }
    }


    public function callback(Request $request)
    {
        $code = $request->code;

        if (!$code) {
            dd([
                'step' => 'NO CODE',
                'request' => $request->all()
            ]);
        }

        /*
        |--------------------------------------
        | 1. GET ACCESS TOKEN (AUTH SERVER)
        |--------------------------------------
        */
        $tokenResponse = Http::timeout(30)->get(
            'https://auth.tiktok-shops.com/api/v2/token/get',
            [
                'app_key' => config('services.tiktok.app_key'),
                'app_secret' => config('services.tiktok.app_secret'),
                'auth_code' => $code,
                'grant_type' => 'authorized_code',
            ]
        );

        $json = $tokenResponse->json();

        if (($json['code'] ?? -1) != 0) {
            dd([
                'STEP' => 'TOKEN ERROR',
                'RESPONSE' => $json
            ]);
        }

        $data = $json['data'];

        $accessToken = $data['access_token'];
        $refreshToken = $data['refresh_token'];
        $openId = $data['open_id'];

        /*
        |--------------------------------------
        | 2. GET SHOP INFO (GLOBAL CLUSTER FIX)
        |--------------------------------------
        */
        $baseUrl = 'https://open-api.tiktokglobalshop.com';
        $path = '/api/shop/get_authorized_shop';
        $timestamp = time();

        $params = [
            'app_key' => config('services.tiktok.app_key'),
            'timestamp' => $timestamp,
            'access_token' => $accessToken,
        ];

        $params['sign'] = $this->makeSign($path, $params);

        $shopResponse = Http::get(
            'https://open-api.tiktokglobalshop.com' . $path,
            $params
        );

        $shopJson = $shopResponse->json();

        /*
        |--------------------------------------
        | SAFE CHECK RESPONSE
        |--------------------------------------
        */
        if (!is_array($shopJson)) {
            dd([
                'STEP' => 'SHOP RESPONSE INVALID',
                'RAW' => $shopResponse->body()
            ]);
        }

        if (($shopJson['code'] ?? -1) != 0) {
            dd([
                'STEP' => 'SHOP ERROR',
                'RESPONSE' => $shopJson,
                'DEBUG_PARAMS' => $params
            ]);
        }

        $shopData = $shopJson['data']['shops'][0] ?? null;

        $shopId = $shopData['shop_id'] ?? null;
        $shopName = $shopData['shop_name'] ?? null;

        /*
        |--------------------------------------
        | SUCCESS RESULT
        |--------------------------------------
        */
        dd([
            'STEP' => 'SUCCESS',
            'open_id' => $openId,
            'shop_id' => $shopId,
            'shop_name' => $shopName,
            'access_token' => $accessToken,
            'refresh_token' => $refreshToken,
            'granted_scopes' => $data['granted_scopes'] ?? [],
            'raw_shop' => $shopJson,
        ]);
    }

    private function makeSign($path, $params)
    {
        $appSecret = config('services.tiktok.app_secret');

        unset($params['sign']);

        ksort($params);

        // 🔥 WAJIB: build query string (bukan concat manual)
        $queryString = http_build_query($params);

        // TikTok style string to sign
        $stringToSign = $appSecret . $path . $queryString . $appSecret;

        return hash_hmac('sha256', $stringToSign, $appSecret);
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

    protected function getTiktokAuthUrl(?int $companyId = null)
    {
        $app_key = config('services.tiktok.app_key');
        $redirect_uri = route('callback.tiktok');

        return "https://auth.tiktok-shops.com/oauth/authorize?" . http_build_query([
            'app_key' => $app_key,
            'response_type' => 'code',
            'redirect_uri' => $redirect_uri,
            'scope' => 'seller.authorization.info,shop.basic_info,order.read',
            'state' => $companyId,
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

    protected function decodeOAuthState(?string $state): array
    {
        if (!$state) {
            return [];
        }

        try {
            $payload = json_decode(Crypt::decryptString($state), true);

            return is_array($payload) ? $payload : [];
        } catch (\Throwable $e) {
            Log::warning('Unable to decode OAuth state', [
                'message' => $e->getMessage(),
            ]);

            return [];
        }
    }
}
