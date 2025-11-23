<?php

namespace App\Plugins;

use App\Modules\Integrations\PlugZapi\Repositories\PlugZapiRepository;


class WhatsApp
{

    protected $repository;

    public function __construct()
    {
        $this->repository = new PlugZapiRepository();
    }

    public function sendMessageText(string $message, string $phone, $user, int $delayTyping = 2): string
    {
        return $this->repository->sendMessageText($message, "55{$phone}", $user, $delayTyping);
    }

    public function sendOnlyLink(string $phone, string $linkUrl, string $title, string $linkDescription): string
    {
        return $this->repository->sendOnlyLink("55{$phone}", $linkUrl, $title, $linkDescription);
    }

    public function sendMessageLink(string $message, string $phone, $user, string $linkUrl, string $title, string $linkDescription, int $delayTyping = 2): string
    {
        return $this->repository->sendMessageLink($message, "55{$phone}", $user, $linkUrl, $title, $linkDescription, $delayTyping);
    }

    public function sendDefaultMessageText(string $student, int $clientId, string $phone, $user): string
    {
        return $this->repository->sendDefaultMessageText($student, $clientId, "55{$phone}", $user);
    }

    public function sendMessageDocument($document, string $phone, string $fileName, int $delayTyping = 2): string
    {
        return $this->repository->sendMessageDocument($document, "55{$phone}", $fileName, $delayTyping);
    }
}
