<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class FcmPushService
{
    private string $serverKey;
    private string $fcmUrl = 'https://fcm.googleapis.com/v1/projects/sapa-jonusa/messages:send';

    public function __construct()
    {
        // Pakai Service Account (FCM v1 API) — lebih aman
        $this->serverKey = config('services.firebase.server_key');
    }

    public function sendToToken(string $fcmToken, string $title, string $body, array $data = []): bool
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->getAccessToken(),
                'Content-Type'  => 'application/json',
            ])->post($this->fcmUrl, [
                'message' => [
                    'token' => $fcmToken,
                    'notification' => [
                        'title' => $title,
                        'body'  => $body,
                    ],
                    'data' => array_map('strval', $data), // FCM data harus string semua
                    'android' => [
                        'priority' => 'high',
                        'notification' => [
                            'channel_id' => 'sapa_high_importance',
                            'sound' => 'default',
                        ],
                    ],
                ],
            ]);

            return $response->successful();
        } catch (\Exception $e) {
            Log::error('FCM Error: ' . $e->getMessage());
            return false;
        }
    }

    // Untuk multi-user sekaligus
    public function sendToMultiple(array $tokens, string $title, string $body, array $data = []): void
    {
        foreach ($tokens as $token) {
            if ($token) {
                $this->sendToToken($token, $title, $body, $data);
            }
        }
    }

    private function getAccessToken(): string
    {
        // Gunakan Google Service Account JSON
        $credentialsPath = storage_path('app/firebase-service-account.json');
        
        $client = new \Google\Client();
        $client->setAuthConfig($credentialsPath);
        $client->addScope('https://www.googleapis.com/auth/firebase.messaging');
        
        $token = $client->fetchAccessTokenWithAssertion();
        return $token['access_token'];
    }
}