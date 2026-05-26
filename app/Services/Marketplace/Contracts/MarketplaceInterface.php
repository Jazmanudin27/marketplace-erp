<?php

namespace App\Services\Marketplace\Contracts;

interface MarketplaceInterface
{
    public function getProducts($account);

    public function getOrders($account);

    public function getOrderDetail($account, $invoice);

    public function syncStock($account, $product);

    public function refreshToken($account);
}
