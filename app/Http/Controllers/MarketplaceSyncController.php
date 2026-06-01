<?php

namespace App\Http\Controllers;

use App\DTOs\OrderDTO;
use App\Models\MarketplaceAccount;
use App\Models\MarketplaceProduct;
use App\Models\Order;
use App\Models\OrderItem;
use App\Services\Marketplace\MarketplaceManager;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class MarketplaceSyncController extends Controller
{
    public function syncProducts($id): RedirectResponse
    {
        try {
            $account = MarketplaceAccount::findOrFail($id);
            $this->authorize('view', $account);
            $syncedCount = 0;

            $service = MarketplaceManager::driver(
                $account->platform
            );

            $products = $service->getProducts($account);

            foreach ($products as $product) {
                foreach (($product['skus'] ?? []) as $sku) {
                    MarketplaceProduct::updateOrCreate(
                        [
                            'marketplace_account_id' => $account->id,
                            'product_id' => $product['id'],
                            'sku_id' => $sku['id'] ?? null,
                        ],
                        [
                            'name' => $product['title'] ?? null,
                            'seller_sku' =>
                                $sku['seller_sku'] ?? null,
                            'price' =>
                                $sku['price']['tax_exclusive_price']
                                ?? 0,
                            'stock' =>
                                $sku['inventory'][0]['quantity']
                                ?? 0,
                            'status' =>
                                $product['status'] ?? null,
                            'image' => null,
                            'raw_data' => json_encode($product),
                            'synced_at' => now(),
                        ]
                    );

                    $syncedCount++;
                }
            }

            return redirect()
                ->route('marketplace.products', $account)
                ->with('success', 'Produk berhasil disinkronkan. Total SKU tersimpan: ' . $syncedCount);

        } catch (\Throwable $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function products(MarketplaceAccount $account): View
    {
        $this->authorize('view', $account);

        $products = MarketplaceProduct::query()
            ->where('marketplace_account_id', $account->id)
            ->latest('synced_at')
            ->latest('id')
            ->paginate(15);

        return view('marketplace.products', compact('account', 'products'));
    }

   public function syncOrders($id): RedirectResponse
{
    try {
        $account = MarketplaceAccount::findOrFail($id);
        $this->authorize('view', $account);

        $service = MarketplaceManager::driver($account->platform);

        if (!method_exists($service, 'getOrders')) {
            return back()->with('error', 'Platform ini belum support sync order');
        }

        // 🔥 FAKE DATA (SUDAH DISAMAKAN DENGAN DTO FIELD)
        $rawOrders = [
            [
                'marketplace_order_id' => 'MP-1001',
                'order_number' => 'INV-1001',
                'customer_name' => 'Budi Santoso',
                'customer_email' => 'budi@mail.com',
                'customer_phone' => '08123456789',
                'subtotal' => 150000,
                'shipping_fee' => 10000,
                'total_amount' => 160000,
                'payment_method' => 'COD',
                'payment_status' => 'paid',
                'order_status' => 'processing',
                'order_date' => now()->subDay(),
                'shipping_address' => [
                    'address' => 'Jl. Merdeka No 10',
                    'city' => 'Bandung',
                ],
                'items' => [
                    [
                        'marketplace_product_id' => 'P-001',
                        'name' => 'Kaos Polos',
                        'qty' => 2,
                        'price' => 50000,
                        'subtotal' => 100000,
                    ],
                    [
                        'marketplace_product_id' => 'P-002',
                        'name' => 'Topi',
                        'qty' => 1,
                        'price' => 50000,
                        'subtotal' => 50000,
                    ],
                ],
            ],
            [
                'marketplace_order_id' => 'MP-1002',
                'order_number' => 'INV-1002',
                'customer_name' => 'Siti Aminah',
                'customer_email' => 'siti@mail.com',
                'customer_phone' => '082233445566',
                'subtotal' => 80000,
                'shipping_fee' => 12000,
                'total_amount' => 92000,
                'payment_method' => 'Transfer',
                'payment_status' => 'pending',
                'order_status' => 'pending',
                'order_date' => now(),
                'shipping_address' => [
                    'address' => 'Jl. Asia Afrika',
                    'city' => 'Jakarta',
                ],
                'items' => [
                    [
                        'marketplace_product_id' => 'P-003',
                        'name' => 'Kemeja',
                        'qty' => 1,
                        'price' => 80000,
                        'subtotal' => 80000,
                    ],
                ],
            ],
        ];

        $syncedCount = 0;
        $skippedCount = 0;

        foreach ($rawOrders as $rawOrder) {

            try {

                // 🔥 langsung pakai fake data
                $orderData = $rawOrder;

               $orderDto = (object) [
    'marketplaceOrderId' => $orderData['marketplace_order_id'],
    'marketplace' => $account->platform,
    'orderNumber' => $orderData['order_number'] ?? $orderData['marketplace_order_id'],
    'customerName' => $orderData['customer_name'],
    'customerEmail' => $orderData['customer_email'],
    'customerPhone' => $orderData['customer_phone'],
    'shippingAddress' => $orderData['shipping_address'],
    'subtotal' => $orderData['subtotal'],
    'shippingFee' => $orderData['shipping_fee'],
    'totalAmount' => $orderData['total_amount'],
    'paymentMethod' => $orderData['payment_method'],
    'paymentStatus' => $orderData['payment_status'],
    'orderStatus' => $orderData['order_status'],
    'orderDate' => $orderData['order_date'],
];
                // 🔥 DEBUG SAFE CHECK


                DB::transaction(function () use ($account, $orderDto, $orderData) {

                    $order = Order::updateOrCreate(
                        [
                            'company_id' => $account->company_id,
                            'marketplace_order_id' => $orderDto->marketplaceOrderId,
                            'marketplace' => $orderDto->marketplace,
                        ],
                        [
                            'order_number' => $orderDto->orderNumber ?? $orderDto->marketplaceOrderId,
                            'customer_name' => $orderDto->customerName ?? 'Customer',
                            'customer_email' => $orderDto->customerEmail,
                            'customer_phone' => $orderDto->customerPhone,
                            'shipping_address' => json_encode($orderDto->shippingAddress),
                            'subtotal' => $orderDto->subtotal,
                            'shipping_fee' => $orderDto->shippingFee,
                            'total_amount' => $orderDto->totalAmount,
                            'payment_method' => $orderDto->paymentMethod ?? 'unknown',
                            'payment_status' => $orderDto->paymentStatus,
                            'order_status' => $orderDto->orderStatus,
                            'order_date' => $this->normalizeOrderDate($orderDto->orderDate),
                            'marketplace_data' => json_encode($orderData),
                        ]
                    );

                    // 🔥 ITEMS
                    foreach (($orderData['items'] ?? []) as $item) {
                        $order->items()->updateOrCreate(
                            [
                                'marketplace_product_id' => $item['marketplace_product_id'] ?? null,
                            ],
                            [
                                'name' => $item['name'] ?? null,
                                'qty' => $item['qty'] ?? 1,
                                'price' => $item['price'] ?? 0,
                                'subtotal' => $item['subtotal'] ?? 0,
                            ]
                        );
                    }
                });

                $syncedCount++;

            } catch (\Throwable $e) {
                $skippedCount++;

                Log::warning('Order sync failed (FAKE)', [
                    'message' => $e->getMessage(),
                ]);
            }
        }

        return redirect()
            ->route('marketplace.orders', $account)
            ->with('success', "Sync fake selesai. Berhasil: {$syncedCount}, dilewati: {$skippedCount}");

    } catch (\Throwable $e) {
        return back()->with('error', $e->getMessage());
    }
}
    public function orders(MarketplaceAccount $account): View
    {
        $this->authorize('view', $account);

        $query = Order::query()
            ->with('items')
            ->where('company_id', $account->company_id)
            ->where('marketplace', $account->platform)
            ->orderByDesc('order_date')
            ->orderByDesc('id');

        $summary = [
            'total' => (clone $query)->count(),
            'pending' => (clone $query)->where('order_status', 'pending')->count(),
            'paid' => (clone $query)->where('payment_status', 'paid')->count(),
            'completed' => (clone $query)->whereIn('order_status', ['completed', 'delivered', 'shipped'])->count(),
        ];

        $orders = $query->paginate(10);

        return view('marketplace.orders', compact('account', 'orders', 'summary'));
    }

    private function resolveOrderPayload($service, MarketplaceAccount $account, array $order): array
    {
        $detail = [];

        if ($account->platform === 'shopee') {
            $orderSn = $order['order_sn'] ?? $order['ordersn'] ?? null;

            if ($orderSn && method_exists($service, 'getOrderDetail')) {
                $detail = (array) $service->getOrderDetail($account, $orderSn);
            }
        }

        if ($account->platform === 'tokopedia') {
            $invoice = $order['invoice_number'] ?? $order['invoice'] ?? $order['order_id'] ?? null;

            if ($invoice && method_exists($service, 'getOrderDetail')) {
                $detail = (array) $service->getOrderDetail($account, $invoice);
            }
        }

        if ($account->platform === 'tiktok') {
            $detail = $order;
        }

        return array_merge($order, $detail);
    }

    private function buildOrderDto(string $platform, array $orderData): OrderDTO
    {
        return match ($platform) {
            'shopee' => OrderDTO::fromShopee($orderData),
            'tokopedia' => OrderDTO::fromTokopedia($orderData),
            'tiktok' => OrderDTO::fromTikTok($orderData),
            default => throw new \Exception('Sinkronisasi pesanan belum didukung untuk platform ini'),
        };
    }

    private function normalizeOrderDate(string|int $orderDate): Carbon
    {
        if (is_numeric($orderDate)) {
            return Carbon::createFromTimestamp((int) $orderDate);
        }

        return Carbon::parse($orderDate);
    }

    private function mapOrderItems(array $orderData): array
    {
        $items = $orderData['item_list'] ?? $orderData['items'] ?? $orderData['products'] ?? [];
        $mappedItems = [];

        foreach ($items as $item) {
            $quantity = (int) ($item['model_quantity_purchased'] ?? $item['quantity'] ?? $item['qty'] ?? 1);
            $price = (float) ($item['model_discounted_price'] ?? $item['item_price'] ?? $item['original_price'] ?? $item['price'] ?? 0);
            $subtotal = (float) ($item['subtotal'] ?? $item['total_price'] ?? $item['item_total'] ?? ($quantity * $price));

            $mappedItems[] = [
                'product_id' => null,
                'marketplace_product_id' => (string) ($item['product_id'] ?? $item['item_id'] ?? $item['model_id'] ?? $item['sku_id'] ?? ''),
                'product_name' => $item['item_name'] ?? $item['name'] ?? $item['product_name'] ?? $item['title'] ?? 'Produk',
                'sku' => $item['model_sku'] ?? $item['seller_sku'] ?? $item['sku'] ?? null,
                'quantity' => $quantity,
                'price' => $price,
                'subtotal' => $subtotal,
                'marketplace_data' => $item,
            ];
        }

        return $mappedItems;
    }
}
