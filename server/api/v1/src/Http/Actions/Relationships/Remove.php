<?php

declare(strict_types=1);

namespace ThePetPark\Http\Actions\Relationships;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Doctrine\DBAL\Connection;
use Exception;
use ThePetPark\Schema;
use ThePetPark\Schema\Relationship as R;

use function json_decode;
use function array_pop;
use function htmlentities;

final class Remove
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

        // For convenience, convert a single resource identifier to an array.
        if ($mask & R::ONE) {
            $data = [$data];
        }
                
        // Note: since this is a to-many relationship, the link MUST be a chain.
        if (is_array($link)) {
            $link = array_pop($link);
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

            if (is_array($link)) {

                // Removing to-many relationships requires a DELETE query.
                list($pivot, $from, $to) = $link;

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