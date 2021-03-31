<?php

declare(strict_types=1);

namespace ThePetPark\Services\JWT;

use Firebase\JWT\JWT;

use function file_get_contents;

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
        $this->secret = file_get_contents($secret);
        $this->alg = $alg;
    }

    public function encode(array $payload): string
    {
        return JWT::encode($payload, $this->secret, $this->alg);
    }
}