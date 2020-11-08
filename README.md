# tiny-router

[![Build Status](https://travis-ci.com/esase/tiny-router.svg?branch=master)](https://travis-ci.com/github/esase/tiny-router/builds)
[![Coverage Status](https://coveralls.io/repos/github/esase/tiny-router/badge.svg?branch=master)](https://coveralls.io/github/esase/tiny-router?branch=master)

**Tiny/Routing** - it's a layer between the outside world like a browser or a console command [CLI](https://en.wikipedia.org/wiki/CLI)
and your application. The package may be integrated to any existing `php` project and it does not require any
extra packages. 

**Usually routing contains of two main parts:**
1. `Routes` - which describe a meta information like - a `query`, a query `type`, and 
a responsible `controller` which processes all incoming requests.

2. A `Router` - just holds registered routes and matches incoming requests with registered routes.   
So it just returns either a matched route or trigger an `Exception` when a route is not found.

Current implementation of routing is very simple but in the same time very powerful. It gets you 
a possibility to work with it using a plain requests (`literal`) and [Regexp](https://en.wikipedia.org/wiki/Regular_expression) based requests, 
also it supports filtering requests by http requests types like: `GET`,  `POST`, etc.  
Also you can use it as a routing for you `CLI` projects.

**So let's check a look an http routing example:**

```php

    // create an instance of the router
    $router = new Router(new RequestHttpParams($_SERVER));

    // a literal `home` route
    $router->registerRoute(new Route(
        '/',
        'HomeController',
        'index'
    ));

    // a literal `users` route which accepts only `GET` and `POST` requests
    $router->registerRoute(new Route(
        '/users',
        'UserController',
        // list of actions
        [ 
            'GET' => 'list',
            'POST' => 'create'
        ]
    ));

    // a more complex example using a `RegExp` rule
    $router->registerRoute(new Route(
        '|^/users/(?P<id>\d+)$|i', // it's matches to: `/users/1`, `/users/300`, etc
        'UserController',
        [
            'GET' => 'view', 
            'DELETE' => 'delete',
        ],
        'regexp', 
        ['id']
    ));

    // now get a matched route 
    $matchedRoutes = $router->getMatchedRoute();
```

## Installation

Run the following to install this library: 

```bash
$ composer require esase/tiny-router
```

## Documentation

https://tiny-docs.readthedocs.io/en/latest/tiny-router/docs/index.html
