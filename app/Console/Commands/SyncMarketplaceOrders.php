<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\MarketplaceAccount;
use App\Services\Marketplace\MarketplaceManager;

class SyncMarketplaceOrders extends Command
{
    /**
     * Nama command yang dipanggil di scheduler
     */
    protected $signature = 'marketplace:sync-orders';

    /**
     * Deskripsi
     */
    protected $description = 'Sync order dari semua marketplace';

    public function handle()
    {
        $this->info('Starting marketplace sync...');

        $accounts = MarketplaceAccount::all();

        foreach ($accounts as $account) {

            try {
                $service = MarketplaceManager::driver($account->marketplace);

                $orders = $service->getOrders($account);

                // TODO: simpan ke database kamu
                // Order::updateOrCreate(...)

                $this->info("Synced: {$account->marketplace} - {$account->shop_id}");

            } catch (\Exception $e) {
                $this->error("Error {$account->marketplace}: " . $e->getMessage());
            }
        }

        $this->info('Sync finished');

        return 0;
    }
}
