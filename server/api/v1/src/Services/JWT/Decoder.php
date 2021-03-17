<?php

declare(strict_types=1);

namespace ThePetPark\Services\JWT;

use Firebase\JWT\JWT;

use function file_get_contents;

/**
 * A wrapper class for the JWT::decode static method.
 */
class Decoder
{
    /** @var string */
    private $secret;

    /** @var array */
    private $allowedAlgs;

    public function __construct(string $secret, array $allowedAlgs)
    {
        $this->secret = file_get_contents($secret);
        $this->allowedAlgs = $allowedAlgs;
    }

    public function decode(string $token): array
    {
        return (array) JWT::decode($token, $this->secret, $this->allowedAlgs);
    }
}