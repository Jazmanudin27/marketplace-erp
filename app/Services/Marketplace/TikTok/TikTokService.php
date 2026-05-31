<?php

namespace App\Services\Marketplace\TikTok;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;

class TikTokService
{
    protected string $appKey;
    protected string $appSecret;

    protected string $baseUrl = 'https://open-api.tiktokglobalshop.com';

    public function __construct()
    {
        $this->appKey = config('services.tiktok.app_key');
        $this->appSecret = config('services.tiktok.app_secret');
    }

    /**
     * OAuth URL
     */
    public function getAuthUrl(): string
    {
        $state = base64_encode(json_encode([
            'company_id' => Auth::user()->company_id,
            'platform' => 'tiktok',
            'time' => time(),
        ]));

        return sprintf(
            'https://services.tiktokshop.com/open/authorize?app_key=%s&state=%s',
            $this->appKey,
            $state
        );
    }

    /**
     * Signature TikTok
     */
    protected function sign(string $path, array $params): string
    {
        unset($params['sign']);

        ksort($params);

        $baseString = $this->appSecret . $path;

        foreach ($params as $key => $value) {
            $baseString .= $key . $value;
        }

        $baseString .= $this->appSecret;

        return hash_hmac(
            'sha256',
            $baseString,
            $this->appSecret
        );
    }

    /**
     * Tukar code jadi access token
     */
    public function getAccessToken(array $request): array
    {
        $path = '/api/v2/token/get';

        $params = [
            'app_key' => $this->appKey,
            'app_secret' => $this->appSecret,
            'auth_code' => $request['code'],
            'grant_type' => 'authorized_code',
        ];

        $response = Http::post(
            $this->baseUrl . $path,
            $params
        );

        $json = $response->json();

        if (
            isset($json['data']) &&
            isset($json['data']['access_token'])
        ) {
            return $json['data'];
        }

        throw new \Exception(
            $json['message'] ?? 'Token TikTok gagal'
        );
    }

    /**
     * Ambil shop_id
     */
    public function getShopInfo(string $accessToken)
    {
        $path = '/authorization/202309/shops';

        $params = [
            'app_key' => $this->appKey,
            'timestamp' => time(),
        ];

        $params['sign'] = $this->sign($path, $params);

        $response = Http::withHeaders([
            'x-tts-access-token' => $accessToken,
            'Content-Type' => 'application/json',
        ])->get(
                $this->baseUrl . $path,
                $params
            );

        dd([
            'url' => $this->baseUrl . $path,
            'status' => $response->status(),
            'body' => $response->json(),
            'raw' => $response->body(),
        ]);
    }
}
