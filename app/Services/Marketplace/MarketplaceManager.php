<?php

namespace App\Services\Marketplace;

use App\Services\Marketplace\Shopee\ShopeeService;
use App\Services\Marketplace\TikTok\TikTokService;
use App\Services\Marketplace\Tokopedia\TokopediaService;

class MarketplaceManager
{
    public static function driver($platform)
    {
        return match ($platform) {

            'shopee' => app(ShopeeService::class),

            'tokopedia' => app(TokopediaService::class),

            'tiktok' => app(TikTokService::class),

            default => throw new \Exception(
                'Marketplace tidak ditemukan'
            )
        };
    }
}
