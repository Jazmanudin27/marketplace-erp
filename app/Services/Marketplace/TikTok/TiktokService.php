<?php

namespace App\Services\Marketplace\TikTok;

use Illuminate\Support\Facades\Http;
use App\Services\Marketplace\Contracts\MarketplaceInterface;

class TiktokService implements MarketplaceInterface
{
    public function getOrders($account)
    {
        $timestamp = time();

        $path = "/api/v2/order/get_order_list";

        $baseString =
            config('services.shopee.partner_id') .
            $path .
            $timestamp .
            $account->access_token .
            $account->shop_id;

        $sign = hash_hmac(
            'sha256',
            $baseString,
            config('services.shopee.partner_key')
        );

        $response = Http::get(
            config('services.shopee.host') . $path,
            [
                'partner_id' => config('services.shopee.partner_id'),

                'timestamp' => $timestamp,

                'access_token' => $account->access_token,

                'shop_id' => $account->shop_id,

                'sign' => $sign,

                'time_range_field' => 'create_time',

                'time_from' => strtotime('-1 day'),

                'time_to' => time(),

                'page_size' => 100
            ]
        );

        return $response->json();
    }

    public function getOrderDetail($account, $invoice)
    {
        return [];
    }

    public function syncStock($account, $product)
    {
        return true;
    }

    public function refreshToken($account)
    {
        return true;
    }
}
