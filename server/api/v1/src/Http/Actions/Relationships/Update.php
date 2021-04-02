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

        $input = json_decode((string) $request->getBody(), true);
        $data = $input['data'] ?? false;

        if ($data === false) {
            // Body is empty, can't continue.
            return $response->withStatus(400);
        }

        $schema = $this->schemas->get($resource);
        $qb = $this->conn->createQueryBuilder();

        list($mask, $related, $link) = $schema->getRelationship($relationship);

        if ($mask & R::ONE) {

            // Data could be null if resetting a to-one relationship.
            if (isset($data['type'], $data['id']) === false && $data !== null) {
                return $response->withStatus(400);
            }

            $value = null;
            
            if ($data !== null) {
                if ($data['type'] !== $related) {
                    return $response->withStatus(400); // Resource types should match.
                }

                $value = $qb->createNamedParameter(htmlentities($data['id'], ENT_QUOTES));;
            }

            if (is_array($link)) {

                list($pivot, $from, $to) = array_pop($link);

                // Before attempting to modify the relationship, check to see
                // if it exists in the first place.
                $sub = $this->conn->createQueryBuilder();
                $exists = '1' === $sub
                    ->select('1')
                    ->from($pivot)
                    ->where($qb->expr()->eq($from, $sub->createNamedParameter($id)))
                    ->execute()
                    ->fetchColumn(0);
                
                if ($exists) {
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