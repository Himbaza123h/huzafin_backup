<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Log;

class SMSService
{
    private $apiUrl;

    public function __construct()
    {
        $this->apiUrl = env('SMS_API_URL');
    }

    // Authenticate and retrieve tokens
    public function authenticate()
    {
        $credentials = [
            'api_username' => env('SMS_API_USERNAME'),
            'api_password' => env('SMS_API_PASSWORD'),
        ];

        $response = Http::post("{$this->apiUrl}/auth", $credentials);

        if ($response->successful()) {
            $data = $response->json();
            if (isset($data['access_token']) && isset($data['expires_at'])) {
                $this->storeTokenData($data);
                Log::info('Authentication successful, tokens stored.');
            } else {
                Log::error('access_token or expires_at not found in API response');
            }
        } else {
            Log::error('Authentication failed', ['error' => $response->json()]);
            return false;
        }

        return true;
    }

    // Store token data in session
    private function storeTokenData($data)
    {
        Session::put('access_token', $data['access_token']);
        Session::put('refresh_token', $data['refresh_token']);
        Session::put('expires_at', $data['expires_at']);
    }

    // Check if token is expired
    public function isTokenExpired()
    {
        $expiresAt = Session::get('expires_at');
        return now()->greaterThan($expiresAt);
    }

    // Refresh the token
    public function refreshToken()
    {
        $refreshToken = Session::get('refresh_token');
        $response = Http::post("{$this->apiUrl}/auth/refresh", ['refresh_token' => $refreshToken]);

        if ($response->successful()) {
            $data = $response->json();
            $this->storeTokenData($data);
            Log::info('Token refreshed successfully.');
        } else {
            Log::error('Token refresh failed', ['error' => $response->json()]);
            return false;
        }

        return true;
    }

    // Send SMS
    public function sendSMS($smsData)
    {
        // Ensure token is valid or authenticate/refresh as needed
        if (!Session::has('access_token') && !$this->authenticate()) {
            return ['error' => 'Authentication failed'];
        }

        if ($this->isTokenExpired() && !$this->refreshToken()) {
            return ['error' => 'Token refresh failed'];
        }

        $accessToken = Session::get('access_token');
        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $accessToken
        ])->post("{$this->apiUrl}/mt/single", $smsData);

        return $response->successful() ? $response->json() : ['error' => $response->json()];
    }
}
