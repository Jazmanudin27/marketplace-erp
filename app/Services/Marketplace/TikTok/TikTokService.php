<?php

namespace App\Services\Marketplace;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;

class TikTokService
{
    protected $baseUrl = 'https://auth.tiktok-shops.com/api/v2';

    public function getAuthUrl()
    {
        $appKey = config('services.tiktok.app_key');

        $redirect = urlencode(route('marketplace.callback'));

        $state = base64_encode(json_encode([
            'company_id' => Auth::user()->company_id,
            'platform' => 'tiktok',
            'time' => time()
        ]));

        return "https://auth.tiktok-shops.com/oauth/authorize?app_key={$appKey}&redirect_uri={$redirect}&state={$state}";
    }

    public function getAccessToken($params)
    {
        $response = Http::get($this->baseUrl . '/token/get', [
            'app_key' => config('services.tiktok.app_key'),
            'app_secret' => config('services.tiktok.app_secret'),
            'auth_code' => $params['code'] ?? null,
            'grant_type' => 'authorized_code',
        ]);

        return $response->json()['data'] ?? null;
    }

    public function getShopInfo($accessToken)
    {
        $response = Http::withHeaders([
            'Access-Token' => $accessToken
        ])->get($this->baseUrl . '/shop/get_shop');

        return $response->json()['data'] ?? null;
    }
}
