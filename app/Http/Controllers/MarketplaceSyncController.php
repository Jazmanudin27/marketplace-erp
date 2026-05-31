<?php

namespace App\Http\Controllers;

use App\Models\MarketplaceAccount;
use App\Models\MarketplaceProduct;
use App\Services\Marketplace\MarketplaceManager;

class MarketplaceSyncController extends Controller
{
    public function syncProducts($id)
    {
        try {

            $account = MarketplaceAccount::findOrFail($id);

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
                }
            }

            // return response()->json([
            //     'success' => true,
            //     'message' => 'Produk berhasil disinkronkan',
            //     'total' => count($products),
            // ]);
            return redirect()->back()->with('success', 'Produk berhasil disinkronkan');

        } catch (\Throwable $e) {

            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ], 500);
        }
    }
}
