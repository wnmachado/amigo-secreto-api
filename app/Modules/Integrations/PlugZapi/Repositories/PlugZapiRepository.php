<?php

namespace App\Modules\Integrations\PlugZapi\Repositories;

use App\Modules\Kemis\Clients\Models\Clients;
use GuzzleHttp\Client;

class PlugZapiRepository
{
    private $url = 'https://api.plugzapi.com.br/instances/';

    public function __construct()
    {
        //
    }

    private function getConectionData()
    {
        $instanceId = $_ENV["PLUGZAPI_INSTANCE_ID"];
        $instanceToken = $_ENV["PLUGZAP_INSTANCE_TOKEN"];
        $token = $_ENV["PLUGZAP_TOKEN"];

        return [
            'instanceId' => $instanceId,
            'instanceToken' => $instanceToken,
            'token' => $token,
        ];
    }

    public function sendMessageText(string $message, string $phone, $user, int $delayTyping = 2): string
    {
        $client = new Client();
        $conectionData = $this->getConectionData();

        $url = $this->url . $conectionData['instanceId'] . '/token/'
            . $conectionData['instanceToken'] . '/send-text';

        $response = $client->post($url, [
            'headers' => [
                'Content-Type' => 'application/json',
                'Client-Token' => $conectionData['token']
            ],
            'json' => [
                'phone' => preg_replace('/[^0-9]/', '', $phone),
                'message' => "*{$user}*\n\n{$message}",
                'delayMessage' => rand(1, 15),
                'delayTyping' => $delayTyping
            ]
        ]);

        return $response->getBody()->getContents();
    }

    public function sendOnlyLink(string $phone, string $linkUrl, string $title, string $linkDescription): string
    {
        $client = new Client();
        $conectionData = $this->getConectionData();

        $url = $this->url . $conectionData['instanceId'] . '/token/'
            . $conectionData['instanceToken'] . '/send-link';

        $response = $client->post($url, [
            'headers' => [
                'Content-Type' => 'application/json',
                'Client-Token' => $conectionData['token']
            ],
            'json' => [
                'phone' => preg_replace('/[^0-9]/', '', $phone),
                'message' => $linkUrl,
                'linkUrl' => $linkUrl,
                'title' => $title,
                'linkDescription' => $linkDescription
            ]
        ]);

        return $response->getBody()->getContents();
    }

    public function sendMessageLink(string $message, string $phone, $user, string $linkUrl, string $title, string $linkDescription, int $delayTyping = 2): string
    {
        $client = new Client();
        $conectionData = $this->getConectionData();

        $url = $this->url . $conectionData['instanceId'] . '/token/'
            . $conectionData['instanceToken'] . '/send-link';

        $response = $client->post($url, [
            'headers' => [
                'Content-Type' => 'application/json',
                'Client-Token' => $conectionData['token']
            ],
            'json' => [
                'phone' => preg_replace('/[^0-9]/', '', $phone),
                'message' => "*{$user}*\n\n{$message}",
                'linkUrl' => $linkUrl,
                'title' => $title,
                'linkDescription' => $linkDescription,
                'delayMessage' => rand(1, 15),
                'delayTyping' => $delayTyping
            ]
        ]);

        return $response->getBody()->getContents();
    }

    public function sendDefaultMessageText(string $student, int $clientId, string $phone, $user): string
    {
        $student = explode(' ', $student)[0];
        $message = "Olá, *" . $student . "*! Essa é uma mensagem automática enviada pela empresa *KEMIS Software House*.";

        return $this->sendMessageText($message, $phone, $user);
    }

    public function sendMessageDocument($document, string $phone, string $fileName, int $delayTyping = 2): string
    {
        $client = new Client();
        $conectionData = $this->getConectionData();

        $url = $this->url . $conectionData['instanceId'] . '/token/'
            . $conectionData['instanceToken'] . '/send-document/pdf';

        $response = $client->post($url, [
            'headers' => [
                'Content-Type' => 'application/json',
                'Client-Token' => $conectionData['token']
            ],
            'json' => [
                'phone' => preg_replace('/[^0-9]/', '', $phone),
                'document' => $document,
                'fileName' => $fileName,
                'delayMessage' => rand(1, 15),
                'delayTyping' => $delayTyping
            ]
        ]);

        return $response->getBody()->getContents();
    }
}
