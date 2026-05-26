<?php

namespace App\Jobs\Sync;

use App\DTOs\OrderDTO;
use App\Models\MarketplaceAccount;
use App\Models\Order;
use App\Models\OrderItem;
use App\Services\Marketplace\MarketplaceManager;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SyncOrdersJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public readonly MarketplaceAccount $account,
    ) {}

    public function handle(): void
    {
        try {
            Log::info('Starting order sync', [
                'platform' => $this->account->platform,
                'shop_id' => $this->account->shop_id,
            ]);

            $service = MarketplaceManager::driver($this->account->platform);
            $orders = $service->getOrders($this->account);

            DB::beginTransaction();

            foreach ($orders as $orderData) {
                $dto = match ($this->account->platform) {
                    'shopee' => OrderDTO::fromShopee($orderData),
                    'tiktok' => OrderDTO::fromTikTok($orderData),
                    'tokopedia' => OrderDTO::fromTokopedia($orderData),
                    default => null,
                };

                if (!$dto) {
                    continue;
                }

                $order = Order::updateOrCreate(
                    [
                        'company_id' => $this->account->company_id,
                        'marketplace_order_id' => $dto->marketplaceOrderId,
                        'marketplace' => $dto->marketplace,
                    ],
                    array_merge($dto->toArray(), [
                        'company_id' => $this->account->company_id,
                    ])
                );

                // Sync order items
                foreach ($dto->items as $itemData) {
                    OrderItem::updateOrCreate(
                        [
                            'order_id' => $order->id,
                            'marketplace_product_id' => $itemData['item_id'] ?? $itemData['product_id'] ?? '',
                        ],
                        [
                            'product_name' => $itemData['item_name'] ?? $itemData['product_name'] ?? '',
                            'sku' => $itemData['model_sku'] ?? $itemData['sku'] ?? '',
                            'quantity' => $itemData['model_quantity_purchased'] ?? $itemData['quantity'] ?? 1,
                            'price' => $itemData['model_original_price'] ?? $itemData['price'] ?? 0,
                            'subtotal' => ($itemData['model_original_price'] ?? $itemData['price'] ?? 0) * ($itemData['model_quantity_purchased'] ?? $itemData['quantity'] ?? 1),
                            'marketplace_data' => $itemData,
                        ]
                    );
                }
            }

            DB::commit();

            Log::info('Order sync completed', [
                'platform' => $this->account->platform,
                'shop_id' => $this->account->shop_id,
                'total_orders' => count($orders),
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Order sync failed', [
                'platform' => $this->account->platform,
                'shop_id' => $this->account->shop_id,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }
}
