<?php

namespace App\Services\Marketplace\Contracts;

interface MarketplaceInterface
{
    public function getProducts($account);

    public function getOrders($account);

    public function getOrderDetail($account, $invoice);

    public function syncStock($account, $product);

    public function refreshToken($account);

    /**
     * Optional: build authorization URL for OAuth flows (if supported)
     *
     * @param array $params
     * @return string
     */
    public function getAuthorizationUrl(array $params = []): string;

    /**
     * Backwards-compatible alias for getAuthorizationUrl
     */
    public function getAuthUrl(array $params = []): string;
}
