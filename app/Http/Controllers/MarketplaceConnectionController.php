<?php

namespace App\Http\Controllers;

use App\Models\MarketplaceAccount;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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

        // Redirect to OAuth URL based on platform
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
        $platform = $request->query('platform');
        $code = $request->query('code');
        $shop_id = $request->query('shop_id');

        if (!$code) {
            return redirect()->route('marketplace.accounts')->withErrors('Koneksi ditolak atau gagal.');
        }

        try {
            $company = Auth::user()->company;

            // Exchange code for tokens (implementation depends on platform)
            $tokens = $this->exchangeCodeForTokens($platform, $code, $shop_id);

            MarketplaceAccount::updateOrCreate(
                [
                    'platform' => $platform,
                    'shop_id' => $shop_id,
                    'company_id' => $company->id,
                ],
                [
                    'shop_name' => $tokens['shop_name'] ?? null,
                    'access_token' => $tokens['access_token'],
                    'refresh_token' => $tokens['refresh_token'] ?? null,
                    'expired_at' => $tokens['expired_at'] ?? null,
                ]
            );

            return redirect()->route('marketplace.accounts')
                ->with('success', ucfirst($platform) . ' berhasil terhubung!');

        } catch (\Exception $e) {
            return redirect()->route('marketplace.accounts')
                ->withErrors('Gagal menghubungkan ' . $platform . ': ' . $e->getMessage());
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

        return "https://accounts.tokopedia.com/authorize?" .
            http_build_query([
                'client_id' => $client_id,
                'response_type' => 'code',
                'redirect_uri' => $redirect_uri,
                'state' => csrf_token(),
            ]);
    }

    protected function getTiktokAuthUrl()
    {
        $client_id = config('services.tiktok.client_id');
        $redirect_uri = route('marketplace.callback');

        return "https://auth.tiktok.com/oauth/authorize?" .
            http_build_query([
                'client_id' => $client_id,
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
