<?php

declare(strict_types=1);

namespace ThePetPark\Services\JWT;

use Firebase\JWT\JWT;

/**
 * A wrapper class for the JWT::encode static menthod.
 */
class Encoder
{
    /** @var string */
    private $secret;

    /** @var string */
    private $alg;

    public function __construct(string $secret, string $alg)
    {
        $this->secret = $secret;
        $this->alg = $alg;
    }

    public function encode(string $payload): string
    {
        return JWT::encode($payload, $this->secret, $this->alg);
    }
}