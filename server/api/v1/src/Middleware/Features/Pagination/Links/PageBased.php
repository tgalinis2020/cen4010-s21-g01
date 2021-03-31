<?php

declare(strict_types=1);

namespace ThePetPark\Middleware\Features\Pagination\Links;

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use ThePetPark\Middleware\Features\Resolver;
use ThePetPark\Schema\Relationship as R;

final class PageBased
{
    /** @var string */
    private $baseUrl;

    public function __construct(string $baseUrl)
    {
        $this->baseUrl = $baseUrl;
    }

    public function __invoke(
        ServerRequestInterface $request,
        ResponseInterface $response,
        callable $next
    ): ResponseInterface {

        $quantity = $request->getAttribute(Resolver::QUANTITY);

        // No need for pagination links if it's only one item.
        if ($quantity & R::ONE) {
            return $next($request, $response);
        }

        $document = $request->getAttribute(Resolver::DOCUMENT);
        $data = $request->getAttribute(Resolver::DATA);

        /*
        $document['links'] += [
            'prev' => null,
            'next' => null,
        ];
        */

        return $next(
            $request->withAttribute(Resolver::DOCUMENT, $document),
            $response
        );
    }
}