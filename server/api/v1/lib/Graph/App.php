<?php

declare(strict_types=1);

namespace ThePetPark\Library\Graph;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Container\ContainerInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Query\QueryBuilder;
use Exception;

use ThePetPark\Library\Graph\Schema\Relationship as R;

use function file_exists;
use function class_exists;
use function in_array;

/**
 * Resource schema container.
 * 
 * Uses DBAL's QueryBuilder to create queries based on the structure of the
 * request.
 * 
 * TODO:    This is a monolithic mess! Break up into smaller, interchangeable
 *          components if time allows for it.
 * 
 * TODO:    In the process of abstracting reftable methods away from app;
 *          for now, app extends reftable.
 * 
 * @author Thomas Galinis <tgalinis2020@fau.edu>
 */
class App extends Schema\ReferenceTable implements RequestHandlerInterface, ResponseFactoryInterface
{
    const ACTION_NOT_IMPL = 0;

    // Context types
    const RESOURCE_CONTEXT     = 0;
    const RELATIONSHIP_CONTEXT = 1;

    // These constants hold request attribute keys that must exist in the
    // request. Except for context, an adapter must map them to routes.
    const PARAM_CONTEXT       = 'graph_context';
    const PARAM_RESOURCE      = 'graph_resource';
    const PARAM_ID            = 'graph_id';
    const PARAM_RELATIONSHIP  = 'graph_relationship';
    
    const SUPPORTED_METHODS = ['GET', 'POST', 'PUT', 'PATCH', 'DELETE'];
    
    const REF_PREFIX = 'r';

    /** @var \Doctrine\DBAL\Query\QueryBuilder */
    protected $qb;

    /** @var Schema\Container */
    protected $schemas;

    /** @var string[] */
    protected $actions = [];

    /** @var int */
    protected $nactions = 0;

    /** @var array */
    protected $settings;

    /** @var \Psr\Container\ContainerInterface */
    protected $container;

    /** @var \Psr\Http\Message\ResponseInterface */
    protected $response;

    public static function create(
        Connection $conn,
        ResponseInterface $response,
        array $settings = [],
        $container = null
    ) {
        $_actions = [];
        $_schemas = [];
        $_nactions = 0;

        if ((($f = $settings['definitions']) !== null) && file_exists($f)) {
            list($actions, $schemas) = (require $f);
        
            foreach ($actions as $action) {
                $_actions[$_nactions++] = $action;
            }
            
            foreach ($schemas as $schema) { 
                $s = Schema::fromArray($schema);
                $_schemas[$s->getType()] = $s;
            }
        } else {
            throw new Exception(
                'Compiled definitions file is required to initialize the Graph. '
                . 'Create a YAML definitions file and use bin/graph to compile them.'
            );
        }

        $self = new self(new Schema\Container($_schemas));

        $self->qb = $conn->createQueryBuilder();
        $self->response = $response;
        $self->container = $container;
        $self->nactions = $_nactions;
        $self->actions = $_actions;
        $pagination = $settings['pagination'] ?? [];

        $self->settings = [
            'definitions' => $settings['definitions'] ?? null,
            'pagination' => [
                'pageSize' => $pagination['pageSize'] ?? 20,
            ],
            'features' => $settings['features'] ?? [
                Features\Pagination\Cursor::class,
                Features\Filters\Simple::class,
                Features\Sorting\Simple::class,
            ],
        ];

        return $self;
    }

    public function getQueryBuilder(): QueryBuilder
    {
        return $this->qb;
    }

    public function getFeatures(): array
    {
        return $this->settings['features'];
    }

    /**
     * Initialize the Graph using a definitions array.
     * Note that if string keys are mapped to actions, a dependency container
     * must be set since the action needs to be instantiated.
     * 
     * If a container was not provided, the action listed in the cachke file
     * must be a fully qualified name of a class that implements the
     * ActionInterface.
     */
    /*private function parseArray(array $definitions)
    {
        list($actions, $schemas) = $definitions;
        
        foreach ($actions as $action) {
            $this->actions[$this->nactions++] = $action;
        }
        
        foreach ($schemas as $schema) {
            $this->addSchema(Schema::fromArray($schema));
        }
    }*/

    /**
     * Adds selections to the query based on this schema's attributes.
     * Use provided enumeration $ref to uniquely identify the selected resource.
     */
    public function prepare(Schema\Reference $source)
    {
        $schema = $source->getSchema();

        // Always select the resource's ID.
        $this->qb->addSelect(sprintf('%1$s.%2$s %1$s_%3$s',
            $source->getRef(),
            $schema->getId(),
            'id'
        ));

        // Add the attributes to the select statement, aliasing the fields
        // as {resource ref}_{resource attribute}
        foreach ($schema->getAttributes() as list($attr, $impl)) {
            $this->qb->addSelect(sprintf(
                '%1$s.%2$s %1$s_%3$s',
                $source->getRef(),
                $impl,
                $attr
            ));
        }
    }

    public function prepareIncluded(Schema\Relationship $relationship, Schema\Reference $parent)
    {
        $this->parentRefs[$relationship->getRef()] = $parent;
        $this->prepare($relationship);
    }

    /**
     * Adds related schema to the query.
     * Creates a new reference in the graph's reference table.
     */
    public function resolve(string $relationship, Schema\Reference $source): Schema\Relationship
    {        
        $related = $this->createRef($relationship, $source); // Reftable
        $link = $related->getLink();

        // BEGIN(DRIVER CODE)
        // TODO: Theoretically resource ownership and aggregation types
        //       should have no effect in relationships following a chain of
        //       relationships. Verify this is true!
        if (is_array($link)) {

            $joinOn = $source->getRef();
            $joinOnField = $source->getSchema()->getId();
            
            foreach ($link as $i => list($pivot, $from, $to)) {
                $pivotEnum = $source->getRef() . '_' . $related->getRef() . '_' . $i; // pivots need their own relation enums
                
                $this->qb->innerJoin($joinOn, $pivot, $pivotEnum, $this->qb->expr()->eq(
                    $joinOn    . '.' . $joinOnField,
                    $pivotEnum . '.' . $from
                ));

                $joinOn = $pivotEnum;
                $joinOnField = $to;
            }

            // TODO: What if the chain doesn't end in the related resource's
            //       ID but in another relationship? Unlikely for this project
            //       but might want to consider other relationship fields in
            //       the future.
            $this->qb->innerJoin(
                $joinOn,
                $related->getSchema()->getImplType(),
                $related->getRef(),
                $this->qb->expr()->eq(
                    $joinOn            . '.' . $joinOnField,
                    $related->getRef() . '.'. $related->getSchema()->getId()
                )
            );

        } else {

            $sourceField  = $source->getRef()  . '.';
            $relatedField = $related->getRef() . '.';

            if ($related->getType() & (R::MANY|R::OWNS)) {
                // foreign key is in related resource (resource owns another if
                // there exists a foreign key in the related resource)
                $sourceField  .= $source->getSchema()->getId();
                $relatedField .= $link;
            } else {
                // foreign key is in this resource (resource is owned by related)
                $sourceField  .= $link;
                $relatedField .= $related->getSchema()->getId();
            }

            $this->qb->innerJoin(
                $source->getRef(),
                $related->getSchema()->getImplType(),
                $related->getRef(),
                $this->qb->expr()->eq($sourceField, $relatedField)
            );

        }
        // END(DRIVER CODE)

        return $related;
    }

    public function getMaxPageSize(): int
    {
        return $this->settings['pagination']['pageSize'];
    }

    public function getSchema(string $resource)
    {
        return $this->schemas->get($resource);
    }
    
    public function createResponse(int $code = 200, string $reasonPhrase = ''): ResponseInterface
    {
        return $this->response
            ->withHeader('Content-Type', 'application/vnd.api+json')
            ->withStatus($code, $reasonPhrase);
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $source = $request->getAttribute(self::PARAM_RESOURCE);
        $schema = $this->schemas->get($source);
        
        if ($schema === null) {
            // TODO: consider making handers that can handle this event.
            return $this->response->withStatus(404);
        }
        
        $method = $request->getMethod();
        
        if (in_array($method, self::SUPPORTED_METHODS) === false) {
            return $this->response->withStatus(505);
        }
        
        $context = $request->getAttribute(self::PARAM_CONTEXT);
        $actionClass = $this->actions[$schema->getActionKey($context, $method)];

        if ($this->container !== null) {
            $action = $this->container->get($actionClass);
        } elseif (class_exists($actionClass)) {
            $action = new $actionClass;
        } else {
            throw new Exception(sprintf(
                'Cannot create instance of %s action',
                $actionClass
            ));
        }
        
        return $action->execute($this, $request);
    }
}
