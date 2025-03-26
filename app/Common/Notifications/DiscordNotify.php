<?php

namespace App\Common\Notifications;

use App\Interfaces\INotify;
use GuzzleHttp\Client;

class DiscordNotify implements INotify
{
    protected $webhookUrl;

    protected $client;

    public function __construct()
    {
        $this->client = new Client();
        $this->webhookUrl = config('services.webhook_url');
    }

    public function send(string $message)
    {
        // send $message
        $res = $this->client->request('POST', $this->webhookUrl, [
            'form_params' => [
                "content" => $message,
            ],
        ]);

        if ($res->getStatusCode() == 200) { // 200 OK
            $response_data = $res->getBody()->getContents();
        }
    }
}