<?php

namespace App\Listeners;

use App\Events\OrderCreated;
use App\Jobs\Marketplace\ProcessMarketplaceOrderJob;
use Illuminate\Contracts\Queue\ShouldQueue;

class ProcessOrderToMarketplace implements ShouldQueue
{
    /**
     * Handle the event.
     */
    public function handle(OrderCreated $event): void
    {
        $order = $event->order;

        // Dispatch job to process order in all connected marketplaces
        foreach ($order->company->marketplaceAccounts as $account) {
            ProcessMarketplaceOrderJob::dispatch($account, $order->toArray());
        }
    }
}
