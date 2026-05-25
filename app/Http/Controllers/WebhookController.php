<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Models\MarketplaceAccount;
use App\Services\Marketplace\MarketplaceManager;

class WebhookController extends Controller
{
    /**
     * Shopee Webhook Handler
     */
    public function shopee(Request $request)
    {
        try {
            // 1. Log raw payload (penting untuk debugging)
            Log::info('Shopee Webhook Received', [
                'headers' => $request->headers->all(),
                'payload' => $request->all(),
            ]);

            // 2. Ambil data payload
            $data = $request->all();

            if (!$data) {
                return response()->json([
                    'success' => false,
                    'message' => 'Empty payload'
                ], 400);
            }

            /**
             * 3. Ambil shop_id dari webhook
             * Shopee biasanya kirim shop_id / shop_id
             */
            $shopId = $data['shop_id'] ?? $data['shopid'] ?? null;

            if (!$shopId) {
                return response()->json([
                    'success' => false,
                    'message' => 'shop_id not found'
                ], 400);
            }

            // 4. Cari akun marketplace
            $account = MarketplaceAccount::where('shop_id', $shopId)
                ->where('marketplace', 'shopee')
                ->first();

            if (!$account) {
                Log::warning('Shopee account not found', [
                    'shop_id' => $shopId
                ]);

                return response()->json([
                    'success' => false,
                    'message' => 'Account not found'
                ], 404);
            }

            // 5. Ambil tipe event webhook
            $eventType = $data['code'] ?? $data['event_type'] ?? null;

            // 6. Handle event
            switch ($eventType) {

                case 'order_status_change':
                case 'order_update':
                case 'order_status':

                    $this->handleOrderUpdate($account, $data);
                    break;

                case 'product_update':
                    $this->handleProductUpdate($account, $data);
                    break;

                default:
                    Log::info('Shopee webhook unhandled event', [
                        'event' => $eventType,
                        'data' => $data
                    ]);
                    break;
            }

            return response()->json([
                'success' => true,
                'message' => 'Webhook processed'
            ]);

        } catch (\Exception $e) {

            Log::error('Shopee Webhook Error', [
                'message' => $e->getMessage(),
                'line' => $e->getLine(),
                'file' => $e->getFile(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Internal server error'
            ], 500);
        }
    }

    /**
     * Handle Order Update
     */
    private function handleOrderUpdate($account, $data)
    {
        try {

            Log::info('Processing Shopee Order Update', $data);

            $service = MarketplaceManager::driver('shopee');

            // contoh ambil detail order dari API Shopee
            $orderId = $data['ordersn'] ?? $data['order_sn'] ?? null;

            if (!$orderId) {
                return;
            }

            $orderDetail = $service->getOrderDetail($account, $orderId);

            // TODO: simpan ke database kamu
            // Order::updateOrCreate(...)

            Log::info('Order synced successfully', [
                'order_id' => $orderId,
                'shop_id' => $account->shop_id
            ]);

        } catch (\Exception $e) {
            Log::error('Order update failed', [
                'message' => $e->getMessage()
            ]);
        }
    }

    /**
     * Handle Product Update
     */
    private function handleProductUpdate($account, $data)
    {
        try {

            Log::info('Processing Shopee Product Update', $data);

            // TODO: sync product dari Shopee API

        } catch (\Exception $e) {
            Log::error('Product update failed', [
                'message' => $e->getMessage()
            ]);
        }
    }
}
