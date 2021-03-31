<?php

declare(strict_types=1);

namespace ThePetPark\Middleware\Features;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Query\QueryBuilder;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use ThePetPark\Library\Graph\Schema;
use ThePetPark\Library\Graph\Schema\ReferenceTable;

/**
 * Selecting resources is a multi-stage processes.
 * The necessary tools to resolve them are initialized here and set as request
 * attributes.
 */
final class Initialization
{
    /** @var \Doctrine\DBAL\Connection */
    private $conn;

    /** @var \ThePetPark\Library\Graph\Schema\Container */
    private $schemas;

    /** @var string */
    private $baseUrl;

    public function __construct(Connection $conn, Schema\Container $schemas, string $baseUrl)
    {
        $this->conn = $conn;
        $this->schemas = $schemas;
        $this->baseUrl = $baseUrl;
    }

    public function __invoke(
        ServerRequestInterface $request,
        ResponseInterface $response,
        callable $next
    ): ResponseInterface { 
        parse_str($request->getUri()->getQuery(), $params);

        /** @var \Slim\Route */
        $route = $request->getAttribute('route');
        
        $resource = $route->getArgument('resource');
        $id = $route->getArgument('id');
        $relationship = $route->getArgument('relationship');

        $refs = new ReferenceTable($this->schemas, $resource);
        $base = $refs->getBaseRef();
        $qb = $this->conn->createQueryBuilder()
            ->select()
            ->distinct()
            ->from($base->getSchema()->getImplType(), (string) $base);

        $links = [];
    
        $links['self'] = $this->baseUrl . '/' . $resource;

        if ($id !== null) {
            $links['self'] .= '/' . $id;
        }

        if ($relationship !== null) {
            $links['related'] = $links['self'];
            $links['self']   .= '/' . $relationship;
        }

        $document = ['jsonapi' => '1.0', 'links' => $links, 'data' => null];

        return $next(
            $request
                ->withAttribute(QueryBuilder::class, $qb)
                ->withAttribute(ReferenceTable::class, $refs)
                ->withAttribute(Resolver::DOCUMENT, $document)
                ->withAttribute(Resolver::PARAMETERS, $params),
            $response
        );
    }
}