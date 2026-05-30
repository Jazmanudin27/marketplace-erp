<?php

namespace App\Services\Marketplace\TikTok;

use Exception;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Services\Marketplace\Contracts\MarketplaceInterface;

class TikTokService implements MarketplaceInterface
{
    protected string $host;
    protected string $appId;
    protected string $appSecret;


    public function __construct()
    {
        $this->host = rtrim(
            config('services.tiktok.host', 'https://open-api.tiktokglobalshop.com'),
            '/'
        );

        $this->appId = config('services.tiktok.app_key');
        $this->appSecret = config('services.tiktok.app_secret');
    }

    protected function ensureValidToken($account): void
    {
        if (!$account->expired_at) {
            return;
        }

        if (now()->addMinutes(5)->gte($account->expired_at)) {
            Log::info('Refreshing TikTok token', [
                'shop_id' => $account->shop_id
            ]);

            $this->refreshToken($account);
            $account->refresh();
        }
    }

   public function getProducts($account)
{
    $this->ensureValidToken($account);

    $path = '/product/202502/products/search';

    $params = [
        'app_key' => $this->appId,
        'timestamp' => time(),
        'shop_id' => $account->shop_id,

        'page_size' => 100,
        'page_token' => '',
    ];

    $body = [
        'status' => 'ALL',
    ];

    $params['sign'] = $this->sign(
        $path,
        $params,
        $body
    );

    $url = $this->host . $path . '?' . http_build_query($params);

    $response = Http::withHeaders([
        'x-tts-access-token' => $account->access_token,
        'Content-Type' => 'application/json',
    ])->post($url, $body);

    dd($response->json());
}

    public function sign(string $path, array $queries, array $body = [])
    {
        unset($queries['sign']);
        unset($queries['access_token']);

        ksort($queries);

        $signString = $this->appSecret . $path;

        foreach ($queries as $key => $value) {
            if (is_array($value) || is_object($value)) {
                continue;
            }

            $signString .= $key . $value;
        }

        if (!empty($body)) {
            $signString .= json_encode(
                $body,
                JSON_UNESCAPED_UNICODE |
                JSON_UNESCAPED_SLASHES
            );
        }

        $signString .= $this->appSecret;

        return hash_hmac(
            'sha256',
            $signString,
            $this->appSecret
        );
    }

    protected function validateResponse($response): array
    {
        if ($response->failed()) {
            Log::error('TikTok HTTP Error', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            throw new Exception('TikTok API request failed');
        }

        $data = $response->json();

        if (isset($data['code']) && $data['code'] !== 0) {
            Log::error('TikTok API Business Error', [
                'response' => $data
            ]);

            throw new Exception($data['message'] ?? 'TikTok API Error');
        }

        return $data;
    }

    public function getOrders($account)
    {
        $this->ensureValidToken($account);

        $path = "/api/v2/order/orders/list";

        $orders = [];
        $page = 1;
        $pageSize = 100;

        do {
            $response = Http::retry(3, 1000)
                ->timeout(30)
                ->get($this->host . $path, [
                    'app_key' => $this->appId,
                    'access_token' => $account->access_token,
                    'page_size' => $pageSize,
                    'page' => $page,
                    'create_time_from' => strtotime('-1 day'),
                    'create_time_to' => time(),
                ]);

            $data = $this->validateResponse($response);

            $responseData = $data['data'] ?? [];
            $orderList = $responseData['orders'] ?? [];

            $orders = array_merge($orders, $orderList);

            $hasMore = $responseData['has_more'] ?? false;
            $page++;

            Log::info('TikTok Orders Synced', [
                'shop_id' => $account->shop_id,
                'total_orders' => count($orderList),
                'page' => $page,
            ]);
        } while ($hasMore);

        return $orders;
    }

    public function getOrderDetail($account, $invoice)
    {
        $this->ensureValidToken($account);

        $path = "/api/v2/order/orders/detail";

        $response = Http::retry(3, 1000)
            ->timeout(30)
            ->get($this->host . $path, [
                'app_key' => $this->appId,
                'access_token' => $account->access_token,
                'order_id' => $invoice,
            ]);

        $data = $this->validateResponse($response);

        return $data['data'] ?? [];
    }

    public function syncStock($account, $product)
    {
        $this->ensureValidToken($account);

        $path = "/api/v2/product/stock/update";

        $response = Http::retry(3, 1000)
            ->timeout(30)
            ->post($this->host . $path, [
                'app_key' => $this->appId,
                'access_token' => $account->access_token,
                'product_id' => $product['product_id'],
                'stock' => $product['stock'],
            ]);

        $data = $this->validateResponse($response);

        Log::info('TikTok Stock Updated', [
            'shop_id' => $account->shop_id,
            'product_id' => $product['product_id'],
        ]);

        return $data;
    }

    public function refreshToken($account)
    {
        $response = Http::get(
            'https://auth.tiktok-shops.com/api/v2/token/refresh',
            [
                'app_key' => $this->appId,
                'app_secret' => $this->appSecret,
                'refresh_token' => $account->refresh_token,
                'grant_type' => 'refresh_token',
            ]
        );

        $json = $response->json();

        if (($json['code'] ?? -1) != 0) {
            throw new Exception($json['message'] ?? 'Refresh token gagal');
        }

        $data = $json['data'];

        $account->update([
            'access_token' => $data['access_token'],
            'refresh_token' => $data['refresh_token'],
            'expired_at' => now()->addSeconds($data['access_token_expire_in']),
        ]);

        return $data;
    }
}
