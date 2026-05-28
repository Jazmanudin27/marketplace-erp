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
            return redirect()
                ->route('marketplace.accounts')
                ->with('error', 'Authorization code tidak ditemukan');
        }

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

            return redirect()
                ->route('marketplace.accounts')
                ->with(
                    'error',
                    $json['message'] ?? 'Gagal mengambil access token'
                );
        }

        $data = $json['data'];

        $companyId = session('company_id')
            ?? Auth::user()->company_id;

        $shopId = '7642983562786998032';

        MarketplaceAccount::updateOrCreate(
            [
                'platform' => 'tiktok',
                'shop_id' => $shopId,
                'company_id' => $companyId,
            ],
            [
                'platform' => 'tiktok',
                'company_id' => $companyId,
                'shop_id' => $shopId,
                'shop_name' => $data['seller_name'] ?? 'TikTok Shop',
                'access_token' => $data['access_token'] ?? null,
                'refresh_token' => $data['refresh_token'] ?? null,
                'expired_at' => $data['access_token_expire_in'],
            ]
        );

    }

    protected function extractShopIdentifiers(array $data): array
    {
        $shopId = $data['shop_id']
            ?? $data['seller_id']
            ?? data_get($data, 'shop.id')
            ?? data_get($data, 'shop.shop_id')
            ?? null;

        $shopCipher = $data['shop_cipher']
            ?? data_get($data, 'shop.shop_cipher')
            ?? null;

        if (!$shopId && $shopCipher) {
            $shopId = $shopCipher;
        }

        if (!$shopCipher && is_string($shopId) && !ctype_digit($shopId)) {
            $shopCipher = $shopId;
        }

        return [
            $shopId ? (string) $shopId : null,
            $shopCipher ? (string) $shopCipher : null,
        ];
    }

    protected function resolveAccessTokenExpiry(array $data): ?\DateTimeInterface
    {
        $expiryValue = $data['access_token_expire_in']
            ?? $data['access_token_expire_at']
            ?? $data['expires_in']
            ?? $data['expires_at']
            ?? null;

        if ($expiryValue === null || $expiryValue === '') {
            return null;
        }

        if (is_numeric($expiryValue)) {
            $raw = (int) $expiryValue;

            if ($raw > 9999999999) {
                $raw = (int) floor($raw / 1000);
            }

            if ($raw >= 946684800 && $raw <= 4102444800) {
                return Carbon::createFromTimestamp($raw);
            }

            return now()->addSeconds($raw);
        }

        try {
            return Carbon::parse($expiryValue);
        } catch (\Throwable $e) {
            return null;
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

    protected function getTiktokAuthUrl(?int $companyId = null)
    {
        $app_key = config('services.tiktok.app_key');
        $redirect_uri = route('callback.tiktok');
        $state = Crypt::encryptString(json_encode([
            'platform' => 'tiktok',
            'company_id' => $companyId,
            'timestamp' => now()->timestamp,
        ]));

        return "https://auth.tiktok-shops.com/oauth/authorize?" . http_build_query([
            'app_key' => $app_key,
            'response_type' => 'code',
            'redirect_uri' => $redirect_uri,
            'scope' => 'shop.basic_info,order.read',
            'state' => $state,
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
