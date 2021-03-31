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
        $relationship = $request->getAttribute('relationship');

        if ($this->schemas->has($resource) === false) {
            // Provided resource doesn't exist.
            return $response->withStatus(404);
        }

        $input = json_decode((string) $request->getBody(), true);
        $data = $input['data'] ?? false;

        if ($data === false) {
            // Body is empty, can't continue.
            return $response->withStatus(400);
        }

        $schema = $this->schemas->get($resource);

        if ($schema->hasRelationship($relationship) === false) {
            // Relationship does not exist.
            return $response->withStatus(404);
        }

        list($mask, $related, $link) = $schema->getRelationship($relationship);

        if ($mask & R::ONE) {

            if (isset($data['type'], $data['id']) === false && $data !== null) {
                return $response->withStatus(400);
            }

            $value = null;
            
            if ($data !== null) {

                if ($data['type'] !== $related) {
                    return $response->withStatus(400);
                }

                $value = $data['id'];
            }

            $qb = $this->conn->createQueryBuilder();

            if ($value !== null) {
                $value = $qb->createNamedParameter(htmlentities($value, ENT_QUOTES));
            }

            if (is_array($link)) {

                list($pivot, $from, $to) = array_pop($link);

                // Before attempting to modify the relationship, check to see
                // if it exists in the first place.
                $sub = $this->conn->createQueryBuilder();
                $exists = $sub
                    ->select("1")
                    ->from($pivot)
                    ->where($qb->expr()->eq($from, $sub->createNamedParameter($id)))
                    ->execute()
                    ->fetchColumn(0);
                
                if ($exists === "1") {
                    $qb->update($pivot)
                        ->set($to, $value)
                        ->where($qb->expr()->eq($from, $qb->createNamedParameter($id)));
                } else {
                    $qb->insert($pivot)
                        ->setValue($to, $value)
                        ->setValue($from, $qb->createNamedParameter($id));
                }


            } else {

                $conditionField = $schema->getId();

                // Need to know where the foreign key exists in direct to-one
                // relationships. If the resource owns the related resource,
                // the foreign key exists in the related resource.
                if ($mask & R::OWNS) {
                    $schema = $this->schemas->get($related);
                    $conditionField = $link;
                }

                $qb->update($schema->getImplType())
                    ->set($link, $value)
                    ->where($qb->expr()->eq($conditionField, $qb->createNamedParameter($id)));
            }

            $qb->execute();

        } else {

            // Updating to-many relationships === full replacement.
            // Delete current associations and insert provided ones.
            // Since this operation is niche and dangerous, it will
            // not be supported for the time being.
            return $response->withStatus(403);

        }

        return $response->withStatus(204);
    }
}