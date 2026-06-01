<?php

namespace App\Services\Marketplace\TikTok;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;

class TikTokService
{
    protected $baseUrl = 'https://auth.tiktok-shops.com/api/v2';
    protected string $baseUrlOrder = 'https://open-api.tiktokglobalshop.com';
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

        $result = $response->json();

        if (($result['code'] ?? -1) !== 0) {
            throw new \Exception(
                $result['message'] ?? 'Gagal mengambil produk TikTok'
            );
        }

        return $result['data']['products'] ?? [];
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

    public function getOrders($account)
    {
        $path = '/order/202309/orders/search';

        $body = [
            'create_time_ge' => (int) now()->subDays(3000)->timestamp,
            'create_time_lt' => (int) now()->timestamp,
        ];

        $query = [
            'app_key' => config('services.tiktok.app_key'),
            'timestamp' => time(),
            'shop_cipher' => $account->shop_cipher,

            'page_size' => 50,
            'page' => 1,
        ];
        $jsonBody = json_encode($body);

        $query['sign'] = $this->generateSignOrder($path, $query, $jsonBody);

        // 🔥 MANUAL BUILD QUERY (IMPORTANT)
        $queryString = '';
        foreach ($query as $key => $value) {
            $queryString .= $key . '=' . $value . '&';
        }
        $queryString = rtrim($queryString, '&');

        $response = Http::withHeaders([
            'x-tts-access-token' => $account->access_token,
            'Content-Type' => 'application/json',
        ])
            ->withBody($jsonBody, 'application/json')
            ->post($this->baseUrlOrder . $path . '?' . $queryString);
        $result = $response->json();
        dd([
            'status' => $response->status(),
            'body' => $response->body(),
        ]);
        if (($result['code'] ?? -1) !== 0) {
            throw new \Exception($result['message'] ?? 'Gagal mengambil orders TikTok');
        }

        return $result['data']['orders']
            ?? $result['data']['order_list']
            ?? $result['data']['list']
            ?? [];
    }

    protected function generateSignOrder(string $path, array $query, string $jsonBody): string
    {
        $secret = config('services.tiktok.app_secret');

        ksort($query);

        $string = $secret . $path;

        foreach ($query as $key => $value) {
            $string .= $key . $value;
        }

        $string .= $jsonBody;
        $string .= $secret;

        return hash_hmac('sha256', $string, $secret);
    }
}
