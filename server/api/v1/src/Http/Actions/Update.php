<?php

declare(strict_types=1);

namespace ThePetPark\Http\Actions;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Doctrine\DBAL\Connection;

use ThePetPark\Schema;
use ThePetPark\Schema\Relationship as R;

use function json_decode;
use function htmlentities;

/**
 * Generates a query using the information provided in the request's
 * attributes. Returns a JSON-API document.
 */
final class Update
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

        if ($this->schemas->has($resource) === false) {
            // Provided resource doesn't exist.
            return $response->withStatus(404);
        }

        // TODO:    Support updating relationships? According to the JSON:API
        //          spec, a PATCH on a resource replaces all current associations.
        //          This might be undesireable for to-many relationships but fine
        //          for to-one.
        //
        //          For now, updating relationships from here will not be
        //          supported. Use relationship endpoints instead.
        if (isset($data['relationships'])) {
            return $response->withStatus(403);
        }

        $input = json_decode((string) $request->getBody(), true);
        $data = $input['data'] ?? null;

        if ($data === null) {
            // Body is empty, can't continue.
            return $response->withStatus(400);
        }

        $type = $data['type'] ?? null;
        $attributes = $data['attributes'] ?? null;

        if ($type === null || $attributes === null || $type !== $resource) {
            // Input document needs to follow the JSON:API spec.
            return $response->withStatus(400);
        }

        $schema = $this->schemas->get($resource);
        $qb = $this->conn->createQueryBuilder()->update($schema->getImplType());

        $values = [];

        foreach ($attributes as $attr => $value) {
            if ($schema->hasAttribute($attr) === false) {
                return $response->withStatus(400);
            }

            $values[$attr] = htmlentities($value);

            $qb->set(
                $schema->getImplAttribute($attr),
                $qb->createNamedParameter($values[$attr])
            );
        }

        $qb->where($qb->expr()->eq($schema->getId(), $qb->createNamedParameter($id)))
            ->execute();

        return $response->withStatus(204);
    }
}