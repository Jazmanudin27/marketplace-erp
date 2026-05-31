<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\MarketplaceAccount;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

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
        $state = Str::random(40);

        session([
            'oauth_platform' => $platform,
            'oauth_company_id' => Auth::user()?->company_id,
            'oauth_state' => $state,
        ]);

        return match ($platform) {
            'shopee' => redirect($this->getShopeeAuthUrl($state)),
            'tiktok' => redirect($this->getTiktokAuthUrl($state)),
            'lazada' => redirect($this->getLazadaAuthUrl($state)),
        };
    }

    public function callback(Request $request)
    {
        try {
            if ($request->query('error') === 'auth_denied') {
                return redirect()
                    ->route('marketplace.accounts')
                    ->with('error', 'Authorization TikTok ditolak oleh user');
            }

            $expectedState = session('oauth_state');
            if ($expectedState && $request->query('state') !== $expectedState) {
                return redirect()
                    ->route('marketplace.accounts')
                    ->with('error', 'Invalid TikTok authorization state');
            }

            $code = $request->query('code');

            if (!$code || $code === 'null') {
                return redirect()
                    ->route('marketplace.accounts')
                    ->with('error', 'Authorization code tidak ditemukan');
            }

            $company = Auth::user()?->company;

            if (!$company && session()->filled('oauth_company_id')) {
                $company = Company::find(session('oauth_company_id'));
            }

            if (!$company) {
                return redirect()
                    ->route('marketplace.accounts')
                    ->with('error', 'Company tidak ditemukan');
            }

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

            $tokenData = $tokenJson['data'] ?? [];
            $accessToken = $tokenData['access_token'] ?? null;

            if (!$accessToken) {
                return redirect()
                    ->route('marketplace.accounts')
                    ->with('error', 'Access token TikTok tidak ditemukan');
            }

            $path = '/authorization/202309/shops';
            $timestamp = time();
            $params = [
                'app_key' => config('services.tiktok.app_key'),
                'timestamp' => $timestamp,
            ];
            $params['sign'] = $this->signTiktokRequest($path, $params);

            $host = rtrim(config('services.tiktok.host', 'https://open-api.tiktokglobalshop.com'), '/');

            $shopResponse = Http::timeout(60)
                ->withHeaders([
                    'x-tts-access-token' => $accessToken,
                    'Content-Type' => 'application/json',
                ])
                ->get($host . $path, $params);

            $shopJson = $shopResponse->json();

            if (($shopJson['code'] ?? -1) != 0) {
                return redirect()
                    ->route('marketplace.accounts')
                    ->with('error', $shopJson['message'] ?? 'Gagal mendapatkan shop TikTok');
            }

            $shopId = data_get($shopJson, 'data.shops.0.id')
                ?? $tokenData['open_id']
                ?? null;

            $shopCipher = data_get($shopJson, 'data.shops.0.cipher')
                ?? $tokenData['open_id']
                ?? null;

            MarketplaceAccount::updateOrCreate(
                [
                    'company_id' => $company->id,
                    'platform' => 'tiktok',
                    'shop_id' => $shopId,
                ],
                [
                    'shop_cipher' => $shopCipher,
                    'shop_name' => $tokenData['seller_name'] ?? 'TikTok Shop',
                    'access_token' => $tokenData['access_token'],
                    'refresh_token' => $tokenData['refresh_token'],
                    'expired_at' => Carbon::createFromTimestamp((int) $tokenData['access_token_expire_in']),
                ]
            );

            session()->forget(['oauth_platform', 'oauth_company_id', 'oauth_state']);

            return redirect()
                ->route('marketplace.accounts')
                ->with('success', 'TikTok Shop berhasil terhubung');
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

    protected function getShopeeAuthUrl(string $state)
    {
        $partnerId = config('services.shopee.partner_id');
        $redirectUri = route('marketplace.callback');

        return 'https://partner.shopeemobile.com/api/v2/oauth/authorize?' . http_build_query([
            'client_id' => $partnerId,
            'response_type' => 'code',
            'redirect_uri' => $redirectUri,
            'state' => $state,
        ]);
    }

    protected function getTiktokAuthUrl(string $state)
    {
        $serviceId = config('services.tiktok.service_id');

        if (!$serviceId) {
            throw new \RuntimeException('TIKTOK_SERVICE_ID belum diisi.');
        }

        $baseUrl = rtrim(
            config('services.tiktok.auth_base_url', 'https://services.tiktokshop.com/open/authorize'),
            '/'
        );

        return $baseUrl . '?' . http_build_query([
            'service_id' => $serviceId,
            'state' => $state,
        ]);
    }

    protected function getLazadaAuthUrl(string $state)
    {
        $clientId = config('services.lazada.client_id');
        $redirectUri = route('marketplace.callback');

        return 'https://auth.lazada.com/oauth/authorize?' . http_build_query([
            'client_id' => $clientId,
            'response_type' => 'code',
            'redirect_uri' => $redirectUri,
            'state' => $state,
        ]);
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
