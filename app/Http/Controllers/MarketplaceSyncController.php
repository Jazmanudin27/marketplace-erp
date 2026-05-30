<?php

namespace App\Http\Controllers;

use App\Models\MarketplaceAccount;
use App\Services\Marketplace\MarketplaceManager;
use Illuminate\Support\Facades\Http;

class MarketplaceSyncController extends Controller
{

    public function syncProducts($id)
    {
        $account = MarketplaceAccount::findOrFail($id);

        $path = '/seller/202309/shops';

        $service = new \App\Services\Marketplace\TikTok\TikTokService();

        $params = [
            'app_key' => config('services.tiktok.app_key'),
            'timestamp' => time(),
        ];

        $params['sign'] = $service->sign($path, $params);

        $response = Http::withHeaders([
            'x-tts-access-token' => $account->access_token,
            'Content-Type' => 'application/json',
        ])->get(
            'https://open-api.tiktokglobalshop.com' . $path,
            $params
        );

        dd([
            'status' => $response->status(),
            'body' => $response->json(),
        ]);
    }
    public function syncProductss($id)
    {

        try {
            $account = MarketplaceAccount::findOrFail($id);
            $service = MarketplaceManager::driver($account->platform);

            $result = $service->getProducts($account);

            return response()->json($result);

        } catch (\Throwable $e) {
            dd([
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);
        }
    }
}
