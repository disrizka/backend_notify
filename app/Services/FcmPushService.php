<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class FcmPushService
{
    private string $projectId = 'sapa-jonusa';

   
    private function getAccessToken(): string
{
    return Cache::remember('fcm_access_token', 3000, function () {
        $credentialsPath = storage_path('app/firebase-service-account.json');

        if (!file_exists($credentialsPath)) {
            throw new \Exception('Firebase service account file tidak ditemukan');
        }

        $credentials = json_decode(file_get_contents($credentialsPath), true);

        $now    = time();
        $claims = [
            'iss'   => $credentials['client_email'],
            'sub'   => $credentials['client_email'],
            'aud'   => 'https://oauth2.googleapis.com/token',
            'iat'   => $now,
            'exp'   => $now + 3600,
            'scope' => 'https://www.googleapis.com/auth/firebase.messaging',
        ];

        $jwt = $this->createJwt($claims, $credentials['private_key']);

        $response = Http::withOptions([
            'verify' => false, // ✅ Tambah ini untuk lokal
        ])->asForm()->post('https://oauth2.googleapis.com/token', [
            'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
            'assertion'  => $jwt,
        ]);

        if (!$response->successful()) {
            throw new \Exception('Gagal dapat access token: ' . $response->body());
        }

        return $response->json('access_token');
    });
}

public function sendToToken(string $fcmToken, string $title, string $body, array $data = []): bool
{
    try {
        $accessToken = $this->getAccessToken();

        $response = Http::withOptions([
            'verify' => false, // ✅ Tambah ini juga
        ])->withHeaders([
            'Authorization' => 'Bearer ' . $accessToken,
            'Content-Type'  => 'application/json',
        ])->post("https://fcm.googleapis.com/v1/projects/{$this->projectId}/messages:send", [
            'message' => [
                'token' => $fcmToken,
                'notification' => [
                    'title' => $title,
                    'body'  => $body,
                ],
                'data' => array_map('strval', $data),
                'android' => [
                    'priority' => 'high',
                    'notification' => [
                        'channel_id' => 'sapa_high_importance',
                        'sound'      => 'default',
                    ],
                ],
            ],
        ]);

        Log::info('FCM V1 Response: ' . $response->body());
        return $response->successful();

    } catch (\Exception $e) {
        Log::error('FCM Exception: ' . $e->getMessage());
        return false;
    }
}

    public function sendToMultiple(array $tokens, string $title, string $body, array $data = []): void
    {
        foreach ($tokens as $token) {
            if ($token) $this->sendToToken($token, $title, $body, $data);
        }
    }


    private function createJwt(array $claims, string $privateKey): string
    {
        $header  = $this->base64UrlEncode(json_encode(['alg' => 'RS256', 'typ' => 'JWT']));
        $payload = $this->base64UrlEncode(json_encode($claims));

        $data      = "$header.$payload";
        $signature = '';

        openssl_sign($data, $signature, $privateKey, 'SHA256');

        return $data . '.' . $this->base64UrlEncode($signature);
    }

    private function base64UrlEncode(string $data): string
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }
}