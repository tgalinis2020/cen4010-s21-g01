<?php

declare(strict_types=1);

namespace ThePetPark\Http\Actions\Relationships;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Doctrine\DBAL\Connection;
use ThePetPark\Library\Graph\Schema;
use ThePetPark\Library\Graph\Schema\Relationship as R;

use function json_encode;
use function json_decode;
use function array_pop;
use function htmlentities;

/**
 * Generates a query using the information provided in the request's
 * attributes. Returns a JSON-API document.
 */
final class Add
{
    /** @var \ThePetPark\Library\Graph\Schema\Container */
    private $schemas;

    /** @var string */
    private $baseUrl;

    /** @var \Doctrine\DBAL\Connection */
    private $conn;

    public function __construct(Connection $conn, Schema\Container $schemas, string $url)
    {
        $this->conn = $conn;
        $this->schemas = $schemas;
        $this->baseUrl = $url;
    }

    public function __invoke(Request $request, Response $response): Response
    {
        $resource = $request->getAttribute('resource');
        $id = $request->getAttribute('id');
        $relationship = $request->getAttribute('relationship');

        if ($this->schemas->has($resource) === false) {
            // Provided resource doesn't exist.
            return $response->withStatus(404);
        }

        $input = json_decode((string) $request->getBody(), true);
        $data = $input['data'] ?? null;

        if ($data === null) {
            // Body is empty, can't continue.
            return $response->withStatus(400);
        }

        $schema = $this->schemas->get($resource);

        if ($schema->hasRelationship($relationship) === false) {
            // Relationship does not exist.
            return $response->withStatus(404);
        }

        list($mask, $related, $link) = $schema->getRelationship($relationship);

        // Clients must issue PATCH requests to set to-one relationships.
        if ($mask & R::ONE) {
            return $response->withStatus(403);
        }

        // First pass: make sure provided resource identifiers are valid.
        foreach ($data as $identifier) {
            if (isset($identifier['id'], $identifier['type']) === false) {
                // Resource identifiers must have an ID and type.
                return $response->withStatus(400);
            }

            if ($identifier['type'] !== $related) {
                // Provided identifier must match related resource type.
                return $response->withStatus(400);
            }
        }

        foreach ($data as $identifier) {
            $qb = $this->conn->createQueryBuilder();

            // TODO:    Only single-dimension relationships are supported.
            //          To-many relationships must be resolved using one pivot table.
            //          This is a reasonable limitation for the time being.
            if (is_array($link)) {

                // Adding to-many relationships requires an INSERT query.
                list($pivot, $from, $to) = array_pop($link);

                $qb->insert($pivot)
                    ->setValue($from, $qb->createNamedParameter($id))
                    ->setValue($to, $qb->createNamedParameter(htmlentities($identifier['id'], ENT_QUOTES)));

            } else {

                // Need to know where the foreign key exists in direct to-many
                // relationships. If the resource owns the related resource,
                // the foreign key exists in the related resource.
                $target = ($mask & R::OWNS) ? $related : $resource;

                // Adding to-one relationships requires an UPDATE query.
                $schema = $this->schemas->get($target);

                $qb->update($schema->getImplType())
                    ->set($link, $qb->createNamedParameter(htmlentities($identifier['id'], ENT_QUOTES)))
                    ->where($qb->expr()->eq($link, $qb->createNamedParameter($id)));
            }

            $qb->execute();
        }

        return $response->withStatus(204);
    }
}