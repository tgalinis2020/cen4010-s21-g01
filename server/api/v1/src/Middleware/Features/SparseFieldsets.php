<?php

declare(strict_types=1);

namespace ThePetPark\Middleware\Features;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use ThePetPark\Schema;

use function explode;

final class SparseFieldsets
{
    /** @var \ThePetPark\Schema\Container */
    private $schemas;

    public function __construct(Schema\Container $schemas)
    {
        $this->schemas = $schemas;
    }

    public function __invoke(
        ServerRequestInterface $request,
        ResponseInterface $response,
        callable $next
    ): ResponseInterface {
        $params = $request->getAttribute('parameters');

        foreach (($params['fields'] ?? []) as $type => $fieldset) {
            if ($this->schemas->has($type)) {
                $schema = $this->schemas->get($type);

                // Deselect all of the resource's attributes; only apply
                // those that are specified in the sparse fieldset. 
                $schema->clearFields();

                foreach (explode(',', $fieldset) as $attr) {
                    if ($schema->hasAttribute($attr)) {
                        $schema->addField($attr);
                    } else {
                        // Silently ignore invalid fields.
                    }
                }
            }
        }

        return $next($request, $response);
    }
}