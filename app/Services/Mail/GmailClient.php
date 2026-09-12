<?php

namespace App\Services\Mail;

use Google_Client;
use Google_Service_Gmail;
use Google\Service\Gmail\Message;
use Illuminate\Support\Facades\File;

class GmailClient
{
    private Google_Service_Gmail $gmail;
    private Google_Client $googleClient;

    public function __construct()
    {
        $this->buildGoogleClient();
    }

    public static function tokenPath(): string
    {
        return storage_path('app/tokens/gmail.json');
    }

    public static function tokenExists(): bool
    {
        return file_exists(self::tokenPath());
    }

    public static function connectedEmail(): ?string
    {
        if (!self::tokenExists()) return null;
        $data = json_decode(file_get_contents(self::tokenPath()), true);
        return $data['email'] ?? null;
    }

    public function sendEmail(string $rawContent): void
    {
        $encoded = strtr(base64_encode($rawContent), ['+' => '-', '/' => '_']);
        $msg = new Message();
        $msg->setRaw($encoded);
        $this->gmail->users_messages->send('me', $msg);
    }

    private function buildGoogleClient(): void
    {
        $this->googleClient = new Google_Client();
        $this->googleClient->setClientId(config('services.google.client_id'));
        $this->googleClient->setClientSecret(config('services.google.client_secret'));

        if (self::tokenExists()) {
            $token = json_decode(file_get_contents(self::tokenPath()), true);
            $this->googleClient->setAccessToken($token);
        }

        if ($this->googleClient->isAccessTokenExpired()) {
            $newToken = $this->googleClient->fetchAccessTokenWithRefreshToken(
                $this->googleClient->getRefreshToken()
            );
            if (!isset($newToken['error'])) {
                $old = json_decode(File::get(self::tokenPath()), true);
                File::put(self::tokenPath(), json_encode(array_merge($old, $newToken)));
            }
        }

        $this->gmail = new Google_Service_Gmail($this->googleClient);
    }
}
