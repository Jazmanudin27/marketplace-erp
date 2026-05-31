<?php

namespace App\Services\Marketplace\TikTok;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;

class TikTokService
{
    protected $baseUrl = 'https://auth.tiktok-shops.com/api/v2';

    public function getAuthorizationUrl(array $params = []): string
    {
        $appKey = config('services.tiktok.app_key');

        $redirect = urlencode(route('marketplace.callback'));

        $state = base64_encode(json_encode([
            'company_id' => Auth::user()?->company_id,
            'platform' => 'tiktok',
            'time' => time(),
        ]));

        return "https://auth.tiktok-shops.com/oauth/authorize?app_key={$appKey}&redirect_uri={$redirect}&state={$state}";
    }

    public function getAuthUrl(array $params = []): string
    {
        return $this->getAuthorizationUrl($params);
    }

    public function exchangeCode(string $code): array
    {
        $data = $this->getAccessToken(['code' => $code]);
        return $data ? (array) $data : [];
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
        $path = '/authorization/202309/shops';

        $params = [
            'app_key' => config('services.tiktok.app_key'),
            'timestamp' => time(),
        ];

        $params['sign'] = $this->generateSign($path, $params);

        $response = Http::withHeaders([
            'x-tts-access-token' => $accessToken,
            'Content-Type' => 'application/json',
        ])->get(
                'https://open-api.tiktokglobalshop.com' . $path,
                $params
            );
        // dd($response->json());
        return $response->json();
    }

    protected function generateSign(string $path, array $params): string
    {
        unset($params['sign']);

        ksort($params);

        $string = config('services.tiktok.app_secret');

        $string .= $path;

        foreach ($params as $key => $value) {
            $string .= $key . $value;
        }

        $string .= config('services.tiktok.app_secret');

        return hash_hmac(
            'sha256',
            $string,
            config('services.tiktok.app_secret')
        );
    }

    public function getProducts($account)
    {
        $path = '/product/202309/products/search';

        $params = [
            'app_key' => config('services.tiktok.app_key'),
            'timestamp' => time(),
            'page_size' => 100,
            'shop_cipher' => $account->shop_cipher,
        ];
        $secret = config('services.tiktok.app_secret');

        $tmp = $params;

        unset($tmp['sign']);

        ksort($tmp);

        $string = $secret . $path;

        foreach ($tmp as $key => $value) {
            $string .= $key . $value;
        }

        $string .= $secret;

        $body = [
            'status' => 'ALL',
        ];

        $params['sign'] = $this->generateProductSign(
            $path,
            $params,
            $body
        );
       
        $response = Http::withHeaders([
            'x-tts-access-token' => $account->access_token,
            'Content-Type' => 'application/json',
        ])->post(
                'https://open-api.tiktokglobalshop.com' .
                $path .
                '?' .
                http_build_query($params),
                $body
            );

        dd([
            'url' => 'https://open-api.tiktokglobalshop.com' . $path,
            'query' => $params,
            'body' => $body,
            'status' => $response->status(),
            'json' => $response->json(),
            'raw' => $response->body(),
        ]);
        // return [
        //     'url' => 'https://open-api.tiktokglobalshop.com' . $path,
        //     'query' => $params,
        //     'body' => $body,
        //     'status' => $response->status(),
        //     'json' => $response->json(),
        // ];
    }

    protected function generateProductSign(
        string $path,
        array $params,
        array $body = []
    ): string {
        unset($params['sign']);

        ksort($params);

        $secret = config('services.tiktok.app_secret');

        $string = $secret . $path;

        foreach ($params as $key => $value) {
            $string .= $key . $value;
        }

        if (!empty($body)) {
            $string .= json_encode(
                $body,
                JSON_UNESCAPED_SLASHES
            );
        }

        $string .= $secret;

        return hash_hmac(
            'sha256',
            $string,
            $secret
        );
    }
}
