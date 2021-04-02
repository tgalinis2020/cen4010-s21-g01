<?php

declare(strict_types=1);

namespace ThePetPark\Middleware\Auth\Guard;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Doctrine\DBAL\Connection;
use ThePetPark\Schema;
use ThePetPark\Middleware\Auth\Session;

/**
 * Protects mutation endpoints from unauthorized users.
 * Session middleware should precede this.
 */
final class Protect
{
    /** @var \Doctrine\DBAL\Connection */
    private $conn;

    /** @var \ThePetPark\Schema\Container */
    private $schemas;

    public function __construct(Connection $conn, Schema\Container $schemas)
    {
        $this->conn = $conn;
        $this->schemas = $schemas;
    }
    
    public function __invoke(Request $request, Response $response, callable $next)
    {
        /** @var \Slim\Route */
        $route = $request->getAttribute('route');
        $schema = $this->schemas->get($route->getArgument('resource'));
        
        if ($schema->hasOwningRelationship()) {
            
            /** @var array */
            $session = $request->getAttribute(Session::TOKEN);
            $qb = $this->conn->createQueryBuilder();
            $id = $qb->createNamedParameter($route->getArgument('id'));

            $ownerID = $qb->select($schema->getOwningRelationship())
                ->from($schema->getImplType())
                ->where($qb->expr()->eq($schema->getId(), $id))
                ->execute()
                ->fetchColumn(0);

            if ($ownerID !== $session['id']) {
                return $response->withStatus(403);
            }

        }

        return $next($request, $response);
    }
}