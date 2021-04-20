<?php

declare(strict_types=1);

namespace ThePetPark\Http\Actions\Relationships;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Doctrine\DBAL\Connection;
use ThePetPark\Schema;
use ThePetPark\Schema\Relationship as R;

use function json_decode;
use function array_pop;
use function htmlentities;

/**
 * Generates a query using the information provided in the request's
 * attributes. Returns a JSON-API document.
 */
final class Add
{
    /** @var \ThePetPark\Schema\Container */
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
        $input = json_decode((string) $request->getBody(), true);
        $data = $input['data'] ?? null;

        if ($data === null) {
            // Body is empty, can't continue.
            return $response->withStatus(400);
        }

        $schema = $this->schemas->get($resource);

        list($mask, $related, $link) = $schema->getRelationship($relationship);

        // Clients must issue PATCH requests to set to-one relationships.
        if ($mask & R::ONE) {
            return $response->withStatus(403);
        }

        // Note: since this is a to-many relationship, the link MUST be a chain.
        if (is_array($link)) {
            $link = array_pop($link);
        }

        // First pass: make sure provided resource identifiers are valid.
        foreach ($data as $obj) {
            if (isset($obj['id'], $obj['type']) === false) {
                // Resource identifiers must have an ID and type.
                return $response->withStatus(400);
            }

            if ($obj['type'] !== $related) {
                // Provided identifier must match related resource type.
                return $response->withStatus(400);
            }
        }

        foreach ($data as $obj) {
            $qb = $this->conn->createQueryBuilder();
            
            $value = $obj['id'] === null ? null : htmlentities($obj['id'], ENT_QUOTES);

            // TODO:    Only single-dimension relationships are supported.
            //          To-many relationships must be resolved using one pivot table.
            //          This is a reasonable limitation for the time being.
            if (is_array($link)) {

                // Adding to-many relationships requires an INSERT query.
                list($pivot, $from, $to) = $link;

                $qb->insert($pivot)
                    ->setValue($from, $qb->createNamedParameter($id))
                    ->setValue($to, $qb->createNamedParameter($value));

            } else {

                // Need to know where the foreign key exists in direct to-many
                // relationships. If the resource owns the related resource,
                // the foreign key exists in the related resource.
                $target = ($mask & R::OWNS) ? $related : $resource;

                // Adding to-one relationships requires an UPDATE query.
                $schema = $this->schemas->get($target);

                $qb->update($schema->getImplType())
                    ->set($link, $qb->createNamedParameter($value))
                    ->where($qb->expr()->eq($link, $qb->createNamedParameter($id)));
            }

            $qb->execute();
        }

        return $response->withStatus(204);
    }
}