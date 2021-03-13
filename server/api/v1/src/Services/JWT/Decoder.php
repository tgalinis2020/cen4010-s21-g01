<?php

declare(strict_types=1);

namespace ThePetPark\Services\JWT;

use Firebase\JWT\JWT;

/**
 * A wrapper class for the JWT::decode static method.
 */
class Decoder
{
    /** @var string */
    private $secret;

    /** @var array */
    private $allowedAlgs;

    public function __construct(string $secret, string $allowedAlgs)
    {
        $this->secret = $secret;
        $this->allowedAlgs = $allowedAlgs;
    }

    public function decode(string $token): array
    {
        return (array) JWT::decode($token, $this->secret, $this->allowedAlgs);
    }
}