<?php

namespace App\Services\Marketplace\Shopee;

use Exception;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Services\Marketplace\Contracts\MarketplaceInterface;

class ShopeeService implements MarketplaceInterface
{
    protected string $host;
    protected string $partnerId;
    protected string $partnerKey;
    public function __construct()
    {
        $this->host = rtrim(
            config('services.shopee.host'),
            '/'
        );

        $this->partnerId =
            config('services.shopee.partner_id');

        $this->partnerKey =
            config('services.shopee.partner_key');
    }

    /*
    |--------------------------------------------------------------------------
    | Ensure Token Valid
    |--------------------------------------------------------------------------
    */

    protected function ensureValidToken($account): void
    {
        if (
            !$account->expired_at
        ) {
            return;
        }

        /*
        |--------------------------------------------------------------------------
        | Refresh Token 5 menit sebelum expired
        |--------------------------------------------------------------------------
        */

        if (
            now()->addMinutes(5)
                ->gte($account->expired_at)
        ) {

            Log::info(
                'Refreshing Shopee token',
                [
                    'shop_id' => $account->shop_id
                ]
            );

            $this->refreshToken($account);

            $account->refresh();
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Generate Signature
    |--------------------------------------------------------------------------
    */

    protected function sign(string $baseString): string
    {
        return hash_hmac(
            'sha256',
            $baseString,
            $this->partnerKey
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Validate Response
    |--------------------------------------------------------------------------
    */

    protected function validateResponse($response): array
    {
        if ($response->failed()) {

            Log::error(
                'Shopee HTTP Error',
                [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]
            );

            throw new Exception(
                'Shopee API request failed'
            );
        }

        $data = $response->json();

        /*
        |--------------------------------------------------------------------------
        | Shopee API Error
        |--------------------------------------------------------------------------
        */

        if (
            isset($data['error']) &&
            $data['error']
        ) {

            Log::error(
                'Shopee API Business Error',
                [
                    'response' => $data
                ]
            );

            throw new Exception(
                $data['message']
                ?? 'Shopee API Error'
            );
        }

        return $data;
    }

    /*
    |--------------------------------------------------------------------------
    | Get Products
    |--------------------------------------------------------------------------
    */

    public function getProducts($account)
    {
        $this->ensureValidToken($account);

        $path = "/api/v2/product/get_item_list";

        $timestamp = time();

        $products = [];

        $cursor = null;

        do {
            $baseString =
                $this->partnerId .
                $path .
                $timestamp .
                $account->access_token .
                $account->shop_id;

            $sign = $this->sign($baseString);

            $params = [
                'partner_id' => $this->partnerId,
                'timestamp' => $timestamp,
                'access_token' => $account->access_token,
                'shop_id' => $account->shop_id,
                'sign' => $sign,
                'page_size' => 100,
            ];

            if ($cursor) {
                $params['cursor'] = $cursor;
            }

            $response = Http::retry(3, 1000)
                ->timeout(30)
                ->get($this->host . $path, $params);

            $data = $this->validateResponse($response);

            $responseData = $data['response'] ?? [];
            $itemList = $responseData['item_list'] ?? [];

            $products = array_merge($products, $itemList);

            $cursor = $responseData['next_cursor'] ?? null;

            Log::info('Shopee Products Synced', [
                'shop_id' => $account->shop_id,
                'total_products' => count($itemList),
                'next_cursor' => $cursor,
            ]);
        } while ($cursor);

        return $products;
    }

    /*
    |--------------------------------------------------------------------------
    | Get Orders
    |--------------------------------------------------------------------------
    */

    public function getOrders($account)
    {
        $this->ensureValidToken($account);

        $path = "/api/v2/order/get_order_list";

        $timestamp = time();

        $orders = [];

        $cursor = null;

        do {

            $baseString =
                $this->partnerId .
                $path .
                $timestamp .
                $account->access_token .
                $account->shop_id;

            $sign = $this->sign($baseString);

            $params = [

                'partner_id' =>
                    $this->partnerId,

                'timestamp' =>
                    $timestamp,

                'access_token' =>
                    $account->access_token,

                'shop_id' =>
                    $account->shop_id,

                'sign' =>
                    $sign,

                'time_range_field' =>
                    'create_time',

                'time_from' =>
                    strtotime('-1 day'),

                'time_to' =>
                    time(),

                'page_size' =>
                    100,
            ];

            /*
            |--------------------------------------------------------------------------
            | Cursor Pagination
            |--------------------------------------------------------------------------
            */

            if ($cursor) {

                $params['cursor'] =
                    $cursor;
            }

            $response = Http::retry(3, 1000)
                ->timeout(30)
                ->get(
                    $this->host . $path,
                    $params
                );

            $data =
                $this->validateResponse(
                    $response
                );

            $responseData =
                $data['response'] ?? [];

            $orderList =
                $responseData['order_list']
                ?? [];

            $orders = array_merge(
                $orders,
                $orderList
            );

            $cursor =
                $responseData['next_cursor']
                ?? null;

            Log::info(
                'Shopee Orders Synced',
                [
                    'shop_id' =>
                        $account->shop_id,

                    'total_orders' =>
                        count($orderList),

                    'next_cursor' =>
                        $cursor,
                ]
            );

        } while ($cursor);

        return $orders;
    }

    /*
    |--------------------------------------------------------------------------
    | Get Order Detail
    |--------------------------------------------------------------------------
    */

    public function getOrderDetail(
        $account,
        $orderSn
    ) {
        $this->ensureValidToken($account);

        $path =
            "/api/v2/order/get_order_detail";

        $timestamp = time();

        $baseString =
            $this->partnerId .
            $path .
            $timestamp .
            $account->access_token .
            $account->shop_id;

        $sign = $this->sign($baseString);

        $response = Http::retry(3, 1000)
            ->timeout(30)
            ->get(
                $this->host . $path,
                [

                    'partner_id' =>
                        $this->partnerId,

                    'timestamp' =>
                        $timestamp,

                    'access_token' =>
                        $account->access_token,

                    'shop_id' =>
                        $account->shop_id,

                    'sign' =>
                        $sign,

                    'order_sn_list' =>
                        $orderSn,

                    'response_optional_fields' =>
                        'item_list,total_amount,buyer_username,recipient_address',
                ]
            );

        $data =
            $this->validateResponse(
                $response
            );

        return $data['response'] ?? [];
    }

    /*
    |--------------------------------------------------------------------------
    | Sync Stock
    |--------------------------------------------------------------------------
    */

    public function syncStock(
        $account,
        $product
    ) {
        $this->ensureValidToken($account);

        $path =
            "/api/v2/product/update_stock";

        $timestamp = time();

        $baseString =
            $this->partnerId .
            $path .
            $timestamp .
            $account->access_token .
            $account->shop_id;

        $sign = $this->sign($baseString);

        $payload = [

            'partner_id' =>
                $this->partnerId,

            'timestamp' =>
                $timestamp,

            'access_token' =>
                $account->access_token,

            'shop_id' =>
                $account->shop_id,

            'sign' =>
                $sign,

            'item_id' =>
                $product['item_id'],

            'stock_list' => [
                [
                    'model_id' =>
                        $product['model_id'],

                    'normal_stock' =>
                        $product['stock'],
                ]
            ]
        ];

        $response = Http::retry(3, 1000)
            ->timeout(30)
            ->post(
                $this->host . $path,
                $payload
            );

        $data =
            $this->validateResponse(
                $response
            );

        Log::info(
            'Shopee Stock Updated',
            [
                'shop_id' =>
                    $account->shop_id,

                'item_id' =>
                    $product['item_id'],
            ]
        );

        return $data;
    }

    /*
    |--------------------------------------------------------------------------
    | Refresh Token
    |--------------------------------------------------------------------------
    */

    public function refreshToken($account)
    {
        $path =
            "/api/v2/auth/access_token/get";

        $timestamp = time();

        $baseString =
            $this->partnerId .
            $path .
            $timestamp;

        $sign = $this->sign(
            $baseString
        );

        $response = Http::retry(3, 1000)
            ->timeout(30)
            ->post(
                $this->host . $path,
                [

                    'partner_id' =>
                        $this->partnerId,

                    'refresh_token' =>
                        $account->refresh_token,

                    'shop_id' =>
                        $account->shop_id,

                    'timestamp' =>
                        $timestamp,

                    'sign' =>
                        $sign,
                ]
            );

        $data =
            $this->validateResponse(
                $response
            );

        $tokenData =
            $data['response'] ?? [];

        if (
            isset(
            $tokenData['access_token']
        )
        ) {

            $account->update([

                'access_token' =>
                    $tokenData['access_token'],

                'refresh_token' =>
                    $tokenData['refresh_token'],

                'expired_at' =>
                    now()->addSeconds(
                        $tokenData['expire_in']
                    ),
            ]);

            Log::info(
                'Shopee Token Refreshed',
                [
                    'shop_id' =>
                        $account->shop_id
                ]
            );
        }

        return $tokenData;
    }

    /**
     * Shopee partner flow does not use a simple redirect OAuth URL here.
     * Provide explicit exception to indicate unsupported operation.
     */
    public function getAuthorizationUrl(array $params = []): string
    {
        throw new \Exception('Shopee authorization via redirect is not supported in this flow.');
    }

    public function getAuthUrl(array $params = []): string
    {
        return $this->getAuthorizationUrl($params);
    }
}
