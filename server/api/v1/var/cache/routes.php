<?php return array (
  0 => 
  array (
    'POST' => 
    array (
      '/upload' => 'route0',
      '/session' => 'route2',
    ),
    'GET' => 
    array (
      '/session' => 'route1',
    ),
    'DELETE' => 
    array (
      '/session' => 'route3',
    ),
  ),
  1 => 
  array (
    'PUT' => 
    array (
      0 => 
      array (
        'regex' => '~^(?|/passwords/([^/]+))$~',
        'routeMap' => 
        array (
          2 => 
          array (
            0 => 'route4',
            1 => 
            array (
              'id' => 'id',
            ),
          ),
        ),
      ),
    ),
    'PATCH' => 
    array (
      0 => 
      array (
        'regex' => '~^(?|/passwords/([^/]+)|/([A-Za-z-]+)/([0-9]+)|/([A-Za-z-]+)/([0-9]+)/relationships/([A-Za-z-]+))$~',
        'routeMap' => 
        array (
          2 => 
          array (
            0 => 'route5',
            1 => 
            array (
              'id' => 'id',
            ),
          ),
          3 => 
          array (
            0 => 'route9',
            1 => 
            array (
              'resource' => 'resource',
              'id' => 'id',
            ),
          ),
          4 => 
          array (
            0 => 'route13',
            1 => 
            array (
              'resource' => 'resource',
              'id' => 'id',
              'relationship' => 'relationship',
            ),
          ),
        ),
      ),
    ),
    'GET' => 
    array (
      0 => 
      array (
        'regex' => '~^(?|/([A-Za-z-]+)|/([A-Za-z-]+)/([0-9]+)|/([A-Za-z-]+)/([0-9]+)/((?!relationships)[A-Za-z-]+))$~',
        'routeMap' => 
        array (
          2 => 
          array (
            0 => 'route6',
            1 => 
            array (
              'resource' => 'resource',
            ),
          ),
          3 => 
          array (
            0 => 'route8',
            1 => 
            array (
              'resource' => 'resource',
              'id' => 'id',
            ),
          ),
          4 => 
          array (
            0 => 'route11',
            1 => 
            array (
              'resource' => 'resource',
              'id' => 'id',
              'relationship' => 'relationship',
            ),
          ),
        ),
      ),
    ),
    'POST' => 
    array (
      0 => 
      array (
        'regex' => '~^(?|/([A-Za-z-]+)|/([A-Za-z-]+)/([0-9]+)/relationships/([A-Za-z-]+))$~',
        'routeMap' => 
        array (
          2 => 
          array (
            0 => 'route7',
            1 => 
            array (
              'resource' => 'resource',
            ),
          ),
          4 => 
          array (
            0 => 'route12',
            1 => 
            array (
              'resource' => 'resource',
              'id' => 'id',
              'relationship' => 'relationship',
            ),
          ),
        ),
      ),
    ),
    'DELETE' => 
    array (
      0 => 
      array (
        'regex' => '~^(?|/([A-Za-z-]+)/([0-9]+)|/([A-Za-z-]+)/([0-9]+)/relationships/([A-Za-z-]+))$~',
        'routeMap' => 
        array (
          3 => 
          array (
            0 => 'route10',
            1 => 
            array (
              'resource' => 'resource',
              'id' => 'id',
            ),
          ),
          4 => 
          array (
            0 => 'route14',
            1 => 
            array (
              'resource' => 'resource',
              'id' => 'id',
              'relationship' => 'relationship',
            ),
          ),
        ),
      ),
    ),
  ),
);