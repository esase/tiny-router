.. _index-router-label:


Routing
=======

Standalone routing implementation for HTTP and console requests.

Installation
------------

Run the following to install this library:


.. code-block:: bash

    $ composer require esase/tiny-router

Route
-----

The :code:`Route()` object is responsible for storing a meta information describing the route.
There is no any difference between **HTTP** and **CLI** routes and there are two types of routes which you may operate:

1. **Literal** - just a fixed string without any dynamic parameters. For the **HTTP** it would be like: :code:`/users/list`, and for the **CLI**: :code:`users import`.
2. **Regexp** - this type allows to write powerful routes using `Regular expression` and include tricky parameters.

---------------
Literal example
---------------

To initialize a **literal** route you only need to create an object and pass: a request identifier, a controller name and the controller's action as well:
We are free to pass the controller's action as a :code:`string` or an :code:`array`.
When we pass the array we assign specific **http** methods to concrete actions in the controller (it may be skipped for the **CLI** mode, there are no **http** methods).
When we pass a string we assume there are no limitations by methods (we accept everything).

.. code-block:: php

    <?php

        use Tiny\Router\Route;

        // http example
        $home = new Route(
            '/',
            'HomeController',
            'index'
        );

        $user = new Route(
            '/users',
            'UserController',
            [
                'GET' => 'list',
                'POST' => 'create'
            ]
        );

        // cli example
        $userImport = new Route(
            'users import',
            'UserCliController',
            'import'
        );

        ...

---------------
Regexp example
---------------

In this case the initialization looks a bit difficult at the first glance.
Instead of a simple string we pass a string in the `regular expression format`. Also we must provide:

- The router's type as: **regexp**.
- We define the list of dynamic parameters which are used in the route, in our first example it's only the **id**.
- The **specification** - is used for assembling requests in regexp routes. The spec is simply a string; replacements are identified using **“%keyname%”**.

.. code-block:: php

    <?php

        use Tiny\Router\Route;

        // http example
        $user = new Route(
            '|^/users/(?P<id>\d+)$|i', // it's matches to: /users/1, /users/300, etc
            'UserController',
            [
                'GET' => 'view',
                'DELETE' => 'delete',
            ],
            'regexp',
            ['id'],
            '/users/%id%'
        );

        // cli example
        $userExport = new Route(
            '|^users export(\s(?P<format>(json|html|xml|rss)))?$|i', // format is optional
            'UserCliController',
            'export',
            'regexp',
            ['format'],
            'users export %format%'
        );
        ...

Router
------

The main idea of the :code:`Router()` is to register, assembling and find matched routes.

------------
Http example
------------

.. code-block:: php

    <?php

        use Tiny\Http\Request;
        use Tiny\Http\RequestHttpParams;
        use Tiny\Router\Route;
        use Tiny\Router\Router;

        // before using the router we must initialize the `Request` object
        $request = new Request(
            new RequestHttpParams($_SERVER) // let's assume our request is: `/users`
        );

        $router = new Router($request);

        // in case when the router cannot find a matched route this one will be returned
        $router->setDefaultRoute(new Route(
            '',
            'NothingFoundController',
            'index'
        ));

        $router->registerRoute(new Route(
            '/users',
            'UserController',
            'list'
        ));

        $matchedRoutes = $router->getMatchedRoute(); // we expect the user's route here
        ...

-----------
CLI example
-----------

.. code-block:: php

    <?php

        use Tiny\Http\Request;
        use Tiny\Http\RequestCliParams;
        use Tiny\Router\Route;
        use Tiny\Router\Router;

        $request = new Request(
            new RequestCliParams($_SERVER) // let's assume our request is: `users import`
        );

        $router = new Router($request);

        $router->registerRoute(new Route(
            'users import',
            'UserCliController',
            'import'
        ));

        $matchedRoutes = $router->getMatchedRoute(); // we expect the user's route here
        ...

-----------------
Universal example
-----------------

In this scenario we build a router which is responsible for working in both modes CLI, and HTTP as well. It means you may have a one single place for all your requests.

.. code-block:: php

    <?php

        use Tiny\Http\Request;
        use Tiny\Http\RequestHttpParams;
        use Tiny\Http\RequestCliParams;
        use Tiny\Router\Route;
        use Tiny\Router\Router;

        $request = new Router\Request(( // auto detect the current mode (CLI or HTTP)
            php_sapi_name() === 'cli'
                ? new RequestCliParams($_SERVER)
                : new RequestHttpParams($_SERVER)
        ));

        $router = new Router($request);

        $router->registerRoute(new Route(
            '/users',
            'UserController',
            'list'
        ));

        $router->registerRoute(new Route(
            'users',
            'UserCliController',
            'list'
        ));

        // now we are ready to accept either HTTP's `/users` or CLI's `users` request
        $matchedRoutes = $router->getMatchedRoute();

------------------------
Assemble request example
------------------------

Assembled requests may be used as a part of links on your web site.

.. code-block:: php

    <?php

        use Tiny\Http\Request;
        use Tiny\Http\RequestHttpParams;
        use Tiny\Router\Route;
        use Tiny\Router\Router;

        $request = new Request(
            new RequestHttpParams($_SERVER)
        );

        $router = new Router($request);

        $router->registerRoute(new Route(
            '/users',
            'UserController',
            'list'
        ));

        $router->registerRoute(new Route(
            '|^/users/(?P<id>\d+)$|i',
            'UserController',
            [
                'GET' => 'view',
                'DELETE' => 'delete',
            ],
            'regexp',
            ['id'],
            '/users/%id%'
        ));

        $router->registerRoute(new Route(
            'users import',
            'UserCliController',
            'import'
        ));

        // prints: `/users`
        $listRequest = $router->assembleRequest(
            'UserController',
            'list'
        );

        // prints: `/users/100`
        $viewRequest = $router->assembleRequest(
            'UserController',
            'view',
            ['id' => 100]
        );

        // prints: `users import`
        $importRequest = $router->assembleRequest(
            'UserCliController',
            'import'
        );