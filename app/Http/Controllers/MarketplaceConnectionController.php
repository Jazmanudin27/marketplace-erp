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
                    ->route('marketplace.accounts')
                    ->with('error', 'Authorization code tidak ditemukan');
            }

            $tokenResponse = Http::get(
                'https://auth.tiktok-shops.com/api/v2/token/get',
                [
                    'app_key' => config('services.tiktok.app_key'),
                    'app_secret' => config('services.tiktok.app_secret'),
                    'auth_code' => $code,
                    'grant_type' => 'authorized_code',
                ]
            );

            $response = $tokenResponse->json();

            if (($response['code'] ?? -1) != 0) {

                return redirect()
                    ->route('marketplace.accounts')
                    ->with('error', $response['message'] ?? 'Gagal koneksi TikTok');
            }

            $data = $response['data'];

            $shopId = $data['shop_id'] ?? null;
            $shopCipher = $data['shop_cipher'] ?? null;

            if (!$shopId && isset($data['seller_id'])) {
                $shopId = $data['seller_id'];
            }

            if (!$shopCipher && isset($data['seller_id']) && !ctype_digit((string) $data['seller_id'])) {
                $shopCipher = $data['seller_id'];
            }

            MarketplaceAccount::updateOrCreate(
                [
                    'platform' => 'tiktok',
                    'shop_id' => $shopId,
                ],
                [
                    'shop_name' => $data['shop_name'] ?? $data['seller_name'] ?? null,
                    'shop_cipher' => $shopCipher,

                    'access_token' => $data['access_token'],

                    'refresh_token' => $data['refresh_token'],

                    'expired_at' => now()->addSeconds($data['access_token_expire_in']),

                    'company_id' => Auth::user()->company_id ?? 1,
                ]
            );

            return redirect()
                ->route('marketplace.accounts')
                ->with('success', 'TikTok Shop berhasil terhubung');

        } catch (\Exception $e) {

            dd([
                'message' => $e->getMessage(),
                'line' => $e->getLine(),
                'file' => $e->getFile(),
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
        $app_key = config('services.tiktok.app_key');

        $redirect_uri = config('services.tiktok.redirect_url');

        return "https://auth.tiktok-shops.com/oauth/authorize?" . http_build_query([
            'app_key' => $app_key,
            'response_type' => 'code',
            'redirect_uri' => $redirect_uri,
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
