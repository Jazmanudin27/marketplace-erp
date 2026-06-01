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
    protected string $redirectUri;

    public function __construct()
    {
        $this->host = rtrim(config('services.tokopedia.host', 'https://fs.tokopedia.net'), '/');
        $this->clientId = config('services.tokopedia.client_id');
        $this->clientSecret = config('services.tokopedia.client_secret');
        $this->redirectUri = config('services.tokopedia.redirect_uri');
    }



    public function exchangeCode(string $code)
    {
        $response = Http::post($this->host . '/token', [
            'grant_type' => 'authorization_code',
            'code' => $code,
            'client_id' => $this->clientId,
            'client_secret' => $this->clientSecret,
        ]);

        return $response->json();
    }

    public function refreshToken($account)
    {
        $response = Http::post($this->host . '/token', [
            'grant_type' => 'refresh_token',
            'client_id' => $this->clientId,
            'client_secret' => $this->clientSecret,
            'refresh_token' => $account->refresh_token,
        ]);

        $data = $response->json();

        if (isset($data['access_token'])) {
            $account->update([
                'access_token' => $data['access_token'],
                'refresh_token' => $data['refresh_token'] ?? $account->refresh_token,
                'expired_at' => now()->addSeconds($data['expires_in']),
            ]);
        }

        return $data;
    }

    protected function ensureValidToken($account): void
    {
        if (!$account->expired_at)
            return;

        if (now()->addMinutes(5)->gte($account->expired_at)) {
            $this->refreshToken($account);
            $account->refresh();
        }
    }

    /* =========================
        RESPONSE VALIDATION
    ========================= */

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
            throw new Exception($data['header']['message'] ?? 'Tokopedia API Error');
        }

        return $data;
    }

    /* =========================
        PRODUCTS
    ========================= */

    public function getProducts($account)
    {
        $this->ensureValidToken($account);

        $page = 0;
        $limit = 50;
        $result = [];

        do {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $account->access_token,
            ])->get($this->host . '/v2/products/fs', [
                        'page' => $page,
                        'per_page' => $limit,
                    ]);

            $data = $this->validateResponse($response);

            $items = $data['data'] ?? [];

            $result = array_merge($result, $items);

            $page++;
        } while (count($items) === $limit);

        return $result;
    }

    /* =========================
        ORDERS
    ========================= */

    public function getOrders($account)
    {
        $this->ensureValidToken($account);

        $page = 0;
        $limit = 50;
        $orders = [];

        do {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $account->access_token,
            ])->get($this->host . '/v2/orders', [
                        'page' => $page,
                        'per_page' => $limit,
                        'from_date' => now()->subDays(1)->format('Y-m-d'),
                        'to_date' => now()->format('Y-m-d'),
                    ]);

            $data = $this->validateResponse($response);

            $items = $data['data'] ?? [];

            $orders = array_merge($orders, $items);

            $page++;
        } while (count($items) === $limit);

        return $orders;
    }

    public function getOrderDetail($account, $invoice)
    {
        $this->ensureValidToken($account);

        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $account->access_token,
        ])->get($this->host . "/v2/orders/{$invoice}");

        $data = $this->validateResponse($response);

        return $data['data'] ?? [];
    }

    /* =========================
        STOCK UPDATE
    ========================= */

    public function syncStock($account, $product)
    {
        $this->ensureValidToken($account);

        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $account->access_token,
        ])->post($this->host . '/v2/products/fs/update', [
                    'product_id' => $product['product_id'],
                    'stock' => $product['stock'],
                ]);

        return $this->validateResponse($response);
    }

    /* =========================
        INTERFACE COMPAT
    ========================= */

    public function getAuthUrl(array $params = []): string
    {
        return $this->getAuthorizationUrl($params);
    }

    /* =========================
       AUTH FLOW
   ========================= */

    public function getAuthorizationUrl(array $params = []): string
    {
        $redirectUri = $params['redirect_uri'] ?? $this->redirectUri;

        if (!$redirectUri) {
            throw new \Exception('Redirect URI Tokopedia belum di-set');
        }

        $query = http_build_query([
            'client_id' => $this->clientId,
            'redirect_uri' => $redirectUri,
            'response_type' => 'code',
            'state' => $params['state'] ?? '',
        ]);
        dd([
            'client_id' => $this->clientId,
            'redirect_uri' => $this->redirectUri,
        ]);
        return "https://accounts.tokopedia.com/authorize?$query";
    }
}
