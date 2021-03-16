<?php

declare(strict_types=1);

namespace ThePetPark\Http\Resources;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Query\QueryBuilder;
use Doctrine\DBAL\Query\Expression\CompositeExpression;
use ThePetPark\Services\Query;

use function json_encode;
use function is_numeric;
use function substr;

/**
 * Idea with this class was to have a generic handler for resource collections.
 * Included resources make this quite challenging however: for to-one relationhips,
 * it would be best to have the related resource included with the base query.
 * 
 * If we were to fetch 20 posts, it would be much more efficient to include
 * their authors along with the post information than make 20 more queries to
 * fetch each post's author.
 * 
 * Resolving many-to-many relationships would be quite troublesome as well --
 * wouldn't want to duplicate returned data.
 */
class ResourceAggregate
{
    /** @var \Doctrine\DBAL\Connection */
    private $conn;

    /** @var array */
    private $graph;

    /**
     * TODO: More data will be required for relationship inclusion!
     *       An array of query builders is not enough.
     */
    public function __construct(
        Connection $conn,
        array $graph
    ) {
        $this->conn = $conn;
        $this->graph = $graph;
    }

    public function __invoke(Request $req, Response $res): Response
    {
        parse_str($req->getUri()->getQuery(), $params);

        // Each resource and relationship needs its own ID to be uniquely
        // identified in queries. Used for table aliasing.
        $relationID = 0;

        $sparse = [];

        $qb = $this->conn->createQueryBuilder();

        // Returns fields as ['realNameInDB' => 'aliasInFrontEnd']
        $fields = $this->schema->getFields();

        $conditions = $qb->expr()->andX();

        $limit = 20;

        // If provided, only select the desired fields.
        if (isset($params['fields'])) {

            $sparse = $params['fields'];
            // TODO: not implemented

        }

        if (isset($sparce[$this->schema->getType()])) {

            $fields = [];

            foreach ($sparce[$this->schema->getType()] as $field) {

                // TODO: validate field
                $fields[] = $field;
            }

        }


        // Pagination parameters. Only cursor-based pagination is supported
        // since it's both easy to impelemnt and very efficient.
        if (isset($params['page'])) {

            $page = $params['page'];

            if (isset($page['cursor'])) {
                $conditions->add($this->qb->expr()->gt(
                    'u.id',
                    $this->qb->createNamedParameter($page['cursor'])
                ));
            }

            if (isset($page['limit']) && is_numeric($page['limit'])) {
                $limit = (int) $params['limit'];
            }

        }

        $qb->setMaxResults($limit);

        /*
        $filters = new Query\Filters($qb, $conditions, $this->fieldMap);

        if (isset($params['filter'])) {

            switch ($filters->apply($params['filter'])) {
                case Query\Filters::EINVALIDFIELD:
                case Query\Filters::EINVALIDEXPR:

                    // If filters are not properly formatted, return a
                    // 400 to the client application.
                    return $res->withStatus(400);
            }
           
        }
        */

        /*if (isset($params['filter'])) {
            foreach ($params['filter'] as $field => $rvalue) {
                $tokens = explode(' ', $rvalue);
    
                switch (count($tokens)) {
                case 1:
                    $tokens = ['eq', $rvalue];
                case 2:
                    list($op, $value) = $tokens;
    
                    if (!isset($this->expressions[$op])) {
                        //return self::EINVALIDEXPR;
                    }
    
                    if (!isset($this->fieldMap[$field])) {
                        //return self::EINVALIDFIELD;
                    }
    
                    // This silly looking block of code calls the filter's
                    // corresponding ExpressionBuilder method.
                    $this->conditions->add(call_user_func(
                        [$this->qb->expr(), self::FILTERS[$op]],
                        $this->fieldMap[$field],
                        $this->qb->createNamedParameter($value)
                    ));
                }
            }
    
            return self::SUCCESS;
        }*/

        if (isset($params['sort'])) {
            $fields = explode(',', $params['sort']);
            $order = 'ASC';

            foreach ($fields as $field) {

                switch (substr($field, 0, 1)) {
                case '-':
                    $field = substr($field, 1);
                    $order = 'DESC';
                    break;
                case '+':
                    $field = substr($field, 1);
                }
                
                if (!isset($this->fieldMap[$field])) {
                    return $res->withStatus(400);
                }
                
                $qb->addOrderBy('u.' . $this->fieldMap[$field], $order);

            }
        }

        $records = $qb->where($conditions)->execute()->fetchAll();

        $res->getBody()->write(json_encode(['data' => $records]));

        return $res->withStatus(count($records) > 0 ? 200 : 404);
    }

    private function getCollection(Request $req, Response $res): Response
    {
        return $res;
    }

    private function getItem(Request $req, Response $res): Response
    {
        return $res;
    }

    private function updateItem(Request $req, Response $res): Response
    {
        return $res;
    }

    private function deleteItem(Request $req, Response $res): Response
    {
        return $res;
    }

    private function createItem(Request $req, Response $res): Response
    {
        return $res;
    }
}

