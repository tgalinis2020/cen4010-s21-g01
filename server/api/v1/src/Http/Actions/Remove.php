<?php

declare(strict_types=1);

namespace ThePetPark\Http\Actions;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Doctrine\DBAL\Connection;
use Exception;
use ThePetPark\Schema;

/**
 * TODO:    Since access control has not been implemented yet, this
 *          behavior might be undesireable (at to be honest, not necessary
 *          for this project's use-case).
 * 
 *          Nevertheless, it is here for completeness.
 */
final class Remove
{
    /** @var \ThePetPark\Schema\Container */
    private $schemas;

    /** @var \Doctrine\DBAL\Connection */
    private $conn;

    public function __construct(Connection $conn, Schema\Container $schemas)
    {
        $this->conn = $conn;
        $this->schemas = $schemas;
    }

    public function __invoke(Request $request, Response $response): Response
    {
        $resource = $request->getAttribute('resource');
        $id = $request->getAttribute('id');
        $schema = $this->schemas->get($resource);
        $qb = $this->conn->createQueryBuilder()->delete($schema->getImplType());

        $qb->where($qb->expr()->eq($schema->getId(), $qb->createNamedParameter($id)));

        try {
            $qb->execute();
        } catch (Exception $e) {
            // TODO:    If the query fails, it is assumed that the provided
            //          ID does not exist in the resource.
            //          This method of exception handling is a catch-all; need
            //          to narrow it down to report more accurate errors.
            return $response->withStatus(404);
        }

        return $response->withStatus(204);
    }
}