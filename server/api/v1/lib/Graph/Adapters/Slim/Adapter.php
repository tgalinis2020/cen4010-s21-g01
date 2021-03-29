<?php

declare(strict_types=1);

namespace ThePetPark\Library\Graph\Adapters\Slim;

use Slim;
use ThePetPark\Library\Graph;

/**
 * Slim 3 Adapter for Graph application.
 * Pass it as the second parameter of a call to Slim::group.
 */
final class Adapter
{
    /** @var string */
    private $idPattern;

    public function __construct(string $idRegex = '[0-9]+')
    {
        $this->idPattern = $idRegex;
    }

    public function __invoke(Slim\App $api)
    {
        $api->group('/{resource:[A-Za-z-]+}[/{id:' . $this->idPattern . '}[/{relationship:(?!relationships)[A-Za-z-]+}]]', function ($r) {
            $r->get('',     Actions\Resolve::class);
            $r->post('',    Actions\Add::class);
            $r->patch('',   Actions\Update::class);
            //$r->delete('',  Actions\Remove::class); // optional
        });
    
        /*
        $api->group('/{resource:[A-Za-z-]+}/{id:' . $this->idPattern . '}/relationships/{relationship:[A-Za-z-]+}', function ($r) {
            $r->get('',     Actions\Relationships\Resolve::class); // optional
            $r->post('',    Actions\Relationships\Add::class);
            $r->patch('',   Actions\Relationships\Update::class); // optional
            $r->delete('',  Actions\Relationships\Remove::class);
        });
        */
    }
}