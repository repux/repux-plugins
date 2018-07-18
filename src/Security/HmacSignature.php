<?php

namespace App\Security;

class HmacSignature
{
    private $sharedSecret;

    public function __construct(string $sharedSecret)
    {
        $this->sharedSecret = $sharedSecret;
    }

    public function isValid(string $signature, array $params): bool
    {
        return $this->generateHmac($params) === $signature;
    }

    public function generateParams(string $storeName): array
    {
        $timestamp = time();

        return [
            'shop' => (string)$storeName,
            'timestamp' => $timestamp,
            'hmac' => $this->generateHmac(
                [
                    'shop' => (string)$storeName,
                    'timestamp' => $timestamp,
                ]
            ),
        ];
    }

    private function generateHmac(array $params): string
    {
        $signatureParts = [];

        foreach ($params as $key => $value) {
            if (in_array($key, ['signature', 'hmac'])) {
                continue;
            }

            $signatureParts[] = $key . '=' . $value;
        }

        natsort($signatureParts);

        return hash_hmac('sha256', implode('&', $signatureParts), $this->sharedSecret);
    }
}
