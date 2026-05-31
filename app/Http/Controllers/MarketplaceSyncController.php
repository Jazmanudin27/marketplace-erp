<?php

namespace App\Http\Controllers;

use App\Models\MarketplaceAccount;
use App\Models\MarketplaceProduct;
use App\Services\Marketplace\MarketplaceManager;
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
}
