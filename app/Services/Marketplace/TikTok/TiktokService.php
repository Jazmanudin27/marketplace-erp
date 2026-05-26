<?php

namespace App\Services\Marketplace\TikTok;

use Exception;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Services\Marketplace\Contracts\MarketplaceInterface;

class TiktokService implements MarketplaceInterface
{
    protected string $host;
    protected string $appId;
    protected string $appSecret;


    public function __construct()
    {
        $this->host = 'https://open-api.tiktokglobalshop.com';

        $this->appId = env('TIKTOK_APP_KEY');

        $this->appSecret = env('TIKTOK_APP_SECRET');
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

    protected function sign(string $baseString): string
    {
        return hash_hmac('sha256', $baseString, $this->appSecret);
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

    public function getProducts($account)
    {
        $this->ensureValidToken($account);

        $path = "/api/v2/product/products/list";

        $products = [];
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
                ]);

            $data = $this->validateResponse($response);

            $responseData = $data['data'] ?? [];
            $productList = $responseData['products'] ?? [];

            $products = array_merge($products, $productList);

            $hasMore = $responseData['has_more'] ?? false;
            $page++;

            Log::info('TikTok Products Synced', [
                'shop_id' => $account->shop_id,
                'total_products' => count($productList),
                'page' => $page,
            ]);
        } while ($hasMore);

        return $products;
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
            'expired_at' => date(
                'Y-m-d H:i:s',
                $data['access_token_expire_in']
            ),
        ]);

        return $data;
    }
}
