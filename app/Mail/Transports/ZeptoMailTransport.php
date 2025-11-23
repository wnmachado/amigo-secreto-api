<?php

namespace App\Mail\Transports;

use Symfony\Component\Mailer\Transport\AbstractTransport;
use Symfony\Component\Mailer\SentMessage;
use Symfony\Component\Mime\Email;
use GuzzleHttp\Client;

class ZeptoMailTransport extends AbstractTransport
{
    protected $client;
    protected $apiKey;

    public function __construct($apiKey)
    {
        parent::__construct();
        $this->apiKey = $apiKey;
        $this->client = new Client();
    }

    protected function doSend(SentMessage $message): void
    {
        /** @var Email $email */
        $email = $message->getOriginalMessage();

        $payload = [
            'from' => [
                'address' => $email->getFrom()[0]->getAddress(),
                'name' => $email->getFrom()[0]->getName(),
            ],
            'to' => array_map(fn($to) => [
                'email_address' => [
                    'address' => $to->getAddress(),
                    'name' => $to->getName(),
                ],
            ], $email->getTo()),
            'cc' => array_map(fn($cc) => [
                'email_address' => [
                    'address' => $cc->getAddress(),
                    'name' => $cc->getName(),
                ],
            ], $email->getCc()),
            'bcc' => array_map(fn($bcc) => [
                'email_address' => [
                    'address' => $bcc->getAddress(),
                    'name' => $bcc->getName(),
                ],
            ], $email->getBcc()),
            'reply_to' => array_map(fn($replyTo) => [
                'email_address' => [
                    'address' => $replyTo->getAddress(),
                    'name' => $replyTo->getName(),
                ],
            ], $email->getReplyTo()),
            'subject' => $email->getSubject(),
            'htmlbody' => $email->getHtmlBody(),
            'textbody' => $email->getTextBody(),
            'attachments' => array_filter(array_map(function($attachment) {
                $body = $attachment->getBody();
                if (empty($body)) {
                    return null;
                }

                // Inferir MIME type da extensão do arquivo
                $filename = $attachment->getFilename();
                $extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
                $mimeTypes = [
                    'pdf' => 'application/pdf',
                    'doc' => 'application/msword',
                    'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                    'xls' => 'application/vnd.ms-excel',
                    'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                    'txt' => 'text/plain',
                    'zip' => 'application/zip',
                    'jpg' => 'image/jpeg',
                    'jpeg' => 'image/jpeg',
                    'png' => 'image/png',
                    'gif' => 'image/gif',
                ];
                $mimeType = isset($mimeTypes[$extension]) ? $mimeTypes[$extension] : 'application/octet-stream';

                return [
                    'name' => $attachment->getFilename(),
                    'content' => base64_encode($body),
                    'mime_type' => $mimeType,
                ];
            }, $email->getAttachments())),
        ];

        $response = $this->client->post('https://api.zeptomail.com/v1.1/email', [
            'headers' => [
                'Authorization' => 'Zoho-enczapikey ' . $this->apiKey,
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ],
            'json' => $payload,
        ]);

        if (!in_array($response->getStatusCode(), [200, 201, 202])) {
            throw new \Exception('Falha ao enviar e-mail pelo ZeptoMail.');
        }
    }

    public function __toString(): string
    {
        return 'zeptomail';
    }
}
