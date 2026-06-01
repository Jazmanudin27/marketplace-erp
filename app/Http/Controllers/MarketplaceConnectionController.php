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

    public function connect(Request $request)
    {
        try {
            $platform = $request->platform;
            $state = base64_encode(json_encode([
                'company_id' => Auth::user()?->company_id,
                'platform' => $platform,
                'time' => time(),
            ]));

            $manager = new MarketplaceManager();
            $driver = $manager->driver($platform);
            $authUrl = $driver->getAuthUrl([
                'redirect_uri' => route('marketplace.callback'),
                'state' => $state,
            ]);

            return redirect()->away($authUrl);
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function callback(Request $request)
    {
        try {
            $state = json_decode(base64_decode($request->state ?? ''), true) ?: [];
            $platform = $state['platform'] ?? $request->platform ?? null;
            $companyId = $state['company_id'] ?? null;

            if (!$platform) {
                return redirect()
                    ->route('marketplace.accounts')
                    ->with('error', 'Platform tidak ditemukan pada callback OAuth');
            }

            $manager = new MarketplaceManager();
            $driver = $manager->driver($platform);

            if ($platform === 'tiktok') {
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
                    return redirect()
                        ->route('marketplace.accounts')
                        ->with('error', $data['message'] ?? 'Gagal mendapatkan token TikTok');
                }

                $tokenData = $data['data'];
                $shopInfo = $driver->getShopInfo($tokenData['access_token']);
                $shop = $shopInfo['data']['shops'][0] ?? null;

                if (!$shop) {
                    return redirect()
                        ->route('marketplace.accounts')
                        ->with('error', 'Shop TikTok tidak ditemukan');
                }

                MarketplaceAccount::updateOrCreate(
                    [
                        'company_id' => $companyId,
                        'platform' => 'tiktok',
                        'shop_id' => $shop['id'],
                    ],
                    [
                        'shop_name' => $shop['name'],
                        'shop_cipher' => $shop['cipher'],
                        'access_token' => $tokenData['access_token'],
                        'refresh_token' => $tokenData['refresh_token'],
                        'expired_at' => date('Y-m-d H:i:s', $tokenData['access_token_expire_in']),
                    ]
                );

                return redirect()
                    ->route('marketplace.accounts')
                    ->with('success', 'TikTok berhasil terhubung');
            }

            if ($platform === 'tokopedia') {
                $tokenData = $driver->exchangeCode($request->code);

                if (!isset($tokenData['access_token'])) {
                    return redirect()
                        ->route('marketplace.accounts')
                        ->with('error', $tokenData['message'] ?? 'Gagal mendapatkan token Tokopedia');
                }

                MarketplaceAccount::updateOrCreate(
                    [
                        'company_id' => $companyId,
                        'platform' => 'tokopedia',
                        'shop_id' => $tokenData['shop_id'] ?? null,
                    ],
                    [
                        'shop_name' => $tokenData['shop_name'] ?? 'Tokopedia',
                        'access_token' => $tokenData['access_token'],
                        'refresh_token' => $tokenData['refresh_token'] ?? null,
                        'expired_at' => isset($tokenData['expires_in'])
                            ? now()->addSeconds($tokenData['expires_in'])
                            : null,
                    ]
                );

                return redirect()
                    ->route('marketplace.accounts')
                    ->with('success', 'Tokopedia berhasil terhubung');
            }

            return redirect()
                ->route('marketplace.accounts')
                ->with('error', 'Platform OAuth belum didukung');
        } catch (\Throwable $e) {
            dd([
                'message' => $e->getMessage(),
                'line' => $e->getLine(),
                'file' => $e->getFile(),
            ]);
        }
    }
    public function disconnect(MarketplaceAccount $account)
    {
        $account->delete();

        return back()->with('success', 'Account berhasil dihapus');
    }
}
