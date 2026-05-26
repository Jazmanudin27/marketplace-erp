<?php

namespace App\Jobs\Sync;

use App\DTOs\ProductDTO;
use App\Models\MarketplaceAccount;
use App\Models\Product;
use App\Services\Marketplace\MarketplaceManager;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SyncProductsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public readonly MarketplaceAccount $account,
    ) {}

    public function handle(): void
    {
        try {
            Log::info('Starting product sync', [
                'platform' => $this->account->platform,
                'shop_id' => $this->account->shop_id,
            ]);

            $service = MarketplaceManager::driver($this->account->platform);
            $products = $service->getProducts($this->account);

            foreach ($products as $productData) {
                $dto = match ($this->account->platform) {
                    'shopee' => ProductDTO::fromShopee($productData),
                    'tiktok' => ProductDTO::fromTikTok($productData),
                    'tokopedia' => ProductDTO::fromTokopedia($productData),
                    default => null,
                };

                if (!$dto) {
                    continue;
                }

                Product::updateOrCreate(
                    [
                        'company_id' => $this->account->company_id,
                        'marketplace_product_id' => $dto->marketplaceProductId,
                        'marketplace' => $dto->marketplace,
                    ],
                    array_merge($dto->toArray(), [
                        'company_id' => $this->account->company_id,
                    ])
                );
            }

            Log::info('Product sync completed', [
                'platform' => $this->account->platform,
                'shop_id' => $this->account->shop_id,
                'total_products' => count($products),
            ]);
        } catch (\Exception $e) {
            Log::error('Product sync failed', [
                'platform' => $this->account->platform,
                'shop_id' => $this->account->shop_id,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }
}
