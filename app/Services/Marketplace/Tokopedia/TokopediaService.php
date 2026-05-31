<?php

namespace App\Services\Marketplace\Tokopedia;

use Exception;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Services\Marketplace\Contracts\MarketplaceInterface;

class TokopediaService implements MarketplaceInterface
{
    protected string $host;
    protected string $clientId;
    protected string $clientSecret;

    public function __construct()
    {
        $this->host = rtrim(config('services.tokopedia.host', 'https://fs.tokopedia.net'), '/');
        $this->clientId = config('services.tokopedia.client_id');
        $this->clientSecret = config('services.tokopedia.client_secret');
    }

    protected function ensureValidToken($account): void
    {
        if (!$account->expired_at) {
            return;
        }

        if (now()->addMinutes(5)->gte($account->expired_at)) {
            Log::info('Refreshing Tokopedia token', [
                'shop_id' => $account->shop_id
            ]);

            $this->refreshToken($account);
            $account->refresh();
        }
    }

    protected function validateResponse($response): array
    {
        if ($response->failed()) {
            Log::error('Tokopedia HTTP Error', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            throw new Exception('Tokopedia API request failed');
        }

        $data = $response->json();

        if (isset($data['header']['error_code']) && $data['header']['error_code'] !== '0') {
            Log::error('Tokopedia API Business Error', [
                'response' => $data
            ]);

            throw new Exception($data['header']['message'] ?? 'Tokopedia API Error');
        }

        return $data;
    }

    public function getProducts($account)
    {
        $this->ensureValidToken($account);

        $path = "/v2/products/fs";

        $products = [];
        $page = 0;
        $pageSize = 100;

        do {
            $response = Http::retry(3, 1000)
                ->timeout(30)
                ->withHeaders([
                    'Authorization' => 'Bearer ' . $account->access_token,
                ])
                ->get($this->host . $path, [
                    'page' => $page,
                    'per_page' => $pageSize,
                ]);

            $data = $this->validateResponse($response);

            $productList = $data['data'] ?? [];

            $products = array_merge($products, $productList);

            $hasMore = count($productList) === $pageSize;
            $page++;

            Log::info('Tokopedia Products Synced', [
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

        $path = "/v2/orders";

        $orders = [];
        $page = 0;
        $pageSize = 100;

        do {
            $response = Http::retry(3, 1000)
                ->timeout(30)
                ->withHeaders([
                    'Authorization' => 'Bearer ' . $account->access_token,
                ])
                ->get($this->host . $path, [
                    'page' => $page,
                    'per_page' => $pageSize,
                    'from_date' => date('Y-m-d', strtotime('-1 day')),
                    'to_date' => date('Y-m-d'),
                ]);

            $data = $this->validateResponse($response);

            $orderList = $data['data'] ?? [];

            $orders = array_merge($orders, $orderList);

            $hasMore = count($orderList) === $pageSize;
            $page++;

            Log::info('Tokopedia Orders Synced', [
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

        $path = "/v2/orders/{$invoice}";

        $response = Http::retry(3, 1000)
            ->timeout(30)
            ->withHeaders([
                'Authorization' => 'Bearer ' . $account->access_token,
            ])
            ->get($this->host . $path);

        $data = $this->validateResponse($response);

        return $data['data'] ?? [];
    }

    public function syncStock($account, $product)
    {
        $this->ensureValidToken($account);

        $path = "/v2/products/fs/update";

        $response = Http::retry(3, 1000)
            ->timeout(30)
            ->withHeaders([
                'Authorization' => 'Bearer ' . $account->access_token,
            ])
            ->post($this->host . $path, [
                'product_id' => $product['product_id'],
                'stock' => $product['stock'],
            ]);

        $data = $this->validateResponse($response);

        Log::info('Tokopedia Stock Updated', [
            'shop_id' => $account->shop_id,
            'product_id' => $product['product_id'],
        ]);

        return $data;
    }

    public function refreshToken($account)
    {
        $path = "/token";

        $response = Http::retry(3, 1000)
            ->timeout(30)
            ->post($this->host . $path, [
                'grant_type' => 'refresh_token',
                'client_id' => $this->clientId,
                'client_secret' => $this->clientSecret,
                'refresh_token' => $account->refresh_token,
            ]);

        $data = $response->json();

        if (isset($data['access_token'])) {
            $account->update([
                'access_token' => $data['access_token'],
                'refresh_token' => $data['refresh_token'],
                'expired_at' => now()->addSeconds($data['expires_in']),
            ]);

            Log::info('Tokopedia Token Refreshed', [
                'shop_id' => $account->shop_id
            ]);
        }

        return $data;
    }

    /**
     * Tokopedia does not support the generic redirect OAuth in this implementation.
     * Provide explicit message for callers.
     */
    public function getAuthorizationUrl(array $params = []): string
    {
        throw new \Exception('Tokopedia authorization via redirect is not supported in this flow.');
    }

    public function getAuthUrl(array $params = []): string
    {
        return $this->getAuthorizationUrl($params);
    }
}
