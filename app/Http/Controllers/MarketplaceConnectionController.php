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
            $platform = $request->platform;
            $manager = new MarketplaceManager();
            $driver = $manager->driver($platform);
            $authUrl = $driver->getAuthUrl();
            return redirect()->away($authUrl);
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function callback(Request $request)
    {
        try {

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
                return redirect()
                    ->route('marketplace.accounts')
                    ->with('error', $data['message'] ?? 'Gagal mendapatkan token TikTok');
            }

            $tokenData = $data['data'];

            $shopInfo = $driver->getShopInfo(
                $tokenData['access_token']
            );

            $shop = $shopInfo['data']['shops'][0];

            if (!$shop) {
                return redirect()
                    ->route('marketplace.accounts')
                    ->with('error', 'Shop TikTok tidak ditemukan');
            }

            $state = json_decode(
                base64_decode($request->state),
                true
            );

            $companyId = $state['company_id'] ?? null;

            $state = json_decode(
                base64_decode($request->state),
                true
            );

            $companyId = $state['company_id'] ?? null;

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
                    'expired_at' => date(
                        'Y-m-d H:i:s',
                        $tokenData['access_token_expire_in']
                    ),
                ]
            );
            return redirect()
                ->route('marketplace.accounts')
                ->with('success', 'TikTok berhasil terhubung');

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
