<?php

declare(strict_types=1);

namespace ThePetPark\Http\Actions\Relationships;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Doctrine\DBAL\Connection;
use Exception;
use ThePetPark\Library\Graph\Schema;
use ThePetPark\Library\Graph\Schema\Relationship as R;

use function json_decode;
use function array_pop;
use function htmlentities;

final class Remove
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

        // For convenience, convert a single resource identifier to an array.
        if ($mask & R::ONE) {
            $data = [$data];
        }

        // First pass: make sure provided resource identifiers are valid before
        // doing anything.
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

            // TODO:    Only single-dimension relationships are supported.
            //          To-many relationships must be resolved using one pivot table.
            //          This is a reasonable limitation for the time being.
            if (is_array($link)) {

                // Removing to-many relationships requires a DELETE query.
                list($pivot, $from, $to) = array_pop($link);

                $qb->delete($pivot)
                    ->where($qb->expr()->andX(
                        $qb->expr()->eq(
                            $from,
                            $qb->createNamedParameter($id)
                        ),
                        $qb->expr()->eq(
                            $to,
                            $qb->createNamedParameter(htmlentities($obj['id'], ENT_QUOTES))
                        )
                    ));

            } else {

                $target = ($mask & R::OWNED) ? $resource : $related;

                // Deleting to-one relationships requires an UPDATE query
                $schema = $this->schemas->get($target);

                $qb->update($schema->getImplType())
                    ->set($link, null)
                    ->where($qb->expr()->eq($link, $qb->createNamedParameter($id)));
            }

            try {
                // TODO:    If the request fails here, need to rollback any
                //          progress made previously. Consider using Doctrine's
                //          transactional features!
                $qb->execute();
            } catch (Exception $e) {
                return $response->withStatus(400);
            }
        }

        return $response->withStatus(204);
    }
}