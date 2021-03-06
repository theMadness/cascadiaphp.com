![](/public/images/logo.svg)
# Welcome to the repository for Cascadia PHP's :mountain: website!

[![Build Status][ico-travis]][link-travis]
[![Coverage Status][ico-scrutinizer]][link-scrutinizer]
[![Quality Score][ico-code-quality]][link-code-quality]

### Quick Reference:

### Request Flow

**Conceptual Flow**  
This repository is powered by a [PSR-15](https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-11-container.md) implemented with [league/container](http://packagist.org/package/league/container).

- Set up a container that has autowiring enabled and is loaded with `\CascadiaPHP\Site\ProviderAggregate` which does essentially all of our bootstrapping.
- Create a [PSR-7](https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-7-http-message.md) HTTP server powered by zend diactoros

Once we have everything bootstrapped, we call `$diactorosServer->listen()` which does the following:

- Resolve a `\Psr\Http\Message\ServerRequestInterface` from the superglobals
- Set up a middleware stack
- Send that request through the middleware stack `request -> ( mw1 -> ( mw2 -> ( mw3 -> response -> ) -> ) -> ) -> response`

That middleware stack contains two special middleware [that resolves request handlers based on registered routes](https://github.com/middlewares/fast-route) and then [uses that request handler to get a response that it can send](https://github.com/middlewares/request-handler). Route handlers can be any callable that returns a `string`, `__toString`, or a `\Psr\Http\Message\ResponseInterface`.

Typical route handlers will either generate and return a response themselves, or will use [PlatesPHP](http://platesphp.com) to render a template into a response. They are set up with **autowiring** so you can simply typehint for the dependencies your route handler wants as arguments, and you will get those object so long as they can be created by the container.

An example of a route handler that renders a template would look like this:

```php
$routeHandler = function(\League\Plates\Engine $plates): \League\Plates\Template\Template {
    return $plates->make('/path/to/template');
};
```

#### Routes
 
Routes in this repository are implemented using [FastRoute](http://github.com/nikic/fastroute). We use [a middleware](https://github.com/middlewares/fast-route) to take a request and match it to a route. Once this route is matched, its handler gets packed into an attribute on the request object. You can see it by getting a copy of the request and checking the attribute:

```php
$request = $container->get(ServerRequestInterface::class);
$requestHandlerCallable = $request->getAttribute('request-handler');
```

The [next middleware in the stack](https://github.com/middlewares/request-handler) acts as a dispatcher. It takes that request and effectively does:

```php
$requestHandlerCallable = $request->getAttribute('request-handler');
$response = $requestHandlerCallable();
```

thereby "resolving" the request into a response.

All routes are registered in `bootstrap/routes.php` which gets an injected `\FastRoute\RouteCollector` allowing you to add routes. An example route could look like this:

```php
// Return a simple string and let the middleware build it into a response
$r->get('/some/path', function(): string { return 'Hello World'; });

// Use a controller method to handle the request
$r->get('/some/other/path', '\Some\Controller::method`);

// Make a simple rand endpoint with password protection
$r->post('/rand', function(\Psr\Http\Message\ServerRequestInterface $request): \Psr\Http\Message\ResponseInterface {
    $post = $request->getParsedBody();
    
    // If the password doesn't match, return a 403
    if ($post['password'] ?? '' === 'trustno1') {
        return new \Zend\Diactoros\Response('A password must be provided.', 403);
    }
    
    // Otherwise return the random number
    return \Zend\Diactoros\Response(mt_rand());
});

// A route that renders a template and returns the resulting string
$r->get('/page', function(\League\Plates\Engine $plates): string {
    return $plates->render('/path/to/template');
});
```

#### Pages

Pages aren't anything fancy, they are simply route handlers that use the [PlatesPHP](http://platesphp.com) engine to generate a response. If you want to add a "page", the steps are as follows:

1. Create a template for it in `templates/pages`, giving it a path correlating directly to the page path for organization (with home being left `home.php`).  
So for example, `/about/contact-us` should be in a template at `templates/pages/about/contact-us.php`
2. Create a minimal controller for it in `src/Controller` keeping the same discipline from step 1 in naming. Using the same contact-us page as an example, our controller would be at `src/Controller/About/ContactUs.php`  
```php
<?php
declare(strict_types=1);

namespace \CascadiaPHP\Site\Controller\About;

class ContactUs
{

    /**
     * Handle the /about/contact-us route
     * @param Engine $plates
     * @return string
     */
    public function mainRouteHandler(Engine $plates): string
    {
        return $plates->render('about/contact-us');
    }
}
```
3. Add a route to `bootstrap/routes.php` mapping `GET` requests to our new controller method.  
```php
$r->get('/about/contact-us', '\CascadiaPHP\Site\Controller\About\ContactUs::mainRouteHandler`);
``` 
4. Start a `SASS` file for the page. We are using AMP which has serious restrictions to the size of the CSS so we have to be mindful and build our CSS per page. `resources/sass/pages/about/contact-us.sass`  
```php
@import "../elements/layout"
@import "../util/_all"
```  
5. Add your new `SASS` file to the build routine in `webpack.mix.js`  
```
mix.sass('resources/sass/pages/about/contact-us.sass', 'resources/css/pages/about')
```
6. Compile using `NPM` or better yet, `Yarn`:
```bash
npm run dev
```
or  
```bash
yarn dev
```

And you're ready to begin working on the new page!


## Compiled Assets
We are using AMP which restricts us from using Javascript really at all beyond predefined [AMP components](https://www.ampproject.org/docs/reference/components). Luckily there is complete coverage of components and they are implemented with performance in mind. And that frees us from ever needing to touch Javascript beyond our build routine.

That said, we still need to manage CSS. AMP requires that any css used on a page be:
- Included inline in a `<style>` tag in the `<head>` of the page
- Less than 50KB per page.

To satisfy that, we are using a subset of [Basscss](http://basscss.com/) and compiling our CSS per page.

We use [Laravel Mix](https://laravel.com/docs/mix) to compile our assets which makes it dead simple. In development you can use `yarn watch` to constantly compile the CSS as you work. See `package.json` for other ways you can compile.

## CascadiaPHP Website Vendor Map

| Type | Vendor | Location | Usage |
| ---- | ------ | -------- | ------ |
| Templates | [PlatesPHP](http://platesphp.com) | [./templates](./templates) | To render `template/sample.php`: `$engine->render('sample')` |
| Content | [parsedown](http://parsedown.org/) | [./content](./content) | To render `content/sample.md`: `$template->markdown('sample')` |
| Routes | [FastRoute](http://github.com/nikic/fastroute) | [./src/Router/ServiceProvider.php](./src/Router/ServiceProvider.php) | `$r->get('path', $callable)` `->get` `->post` `->put` `->delete`
| Controllers | Custom | [./src/Controller](./src/Controller) | There is no defined structure to controllers |
| Container | [league/container](http://packagist.org/package/league/container) | Created in [./dispatcher.php](./dispatcher.php) | `$container->get($binding)` |
| Cache | [cache/filesystem-adapter](http://packagist.org/package/cache/filesystem-adapter) | N/a | PSR-16: `$container->get(\Psr\SimpleCache\CacheInterface::class)` |

# Running local copy

## Frontend assets
Ensure you have npm and yarn running, if you miss npm you're better off looking up installations instructions for your own platform, to install yarn globally (once you procured npm) simply run `npm install --global yarn`.

To install the local dependencies then run `yarn`.

To get the assets built as you work on them, `yarn watch` will set up a continuous task that compiles sass files and possibly other assets.

## PHP dependencies
Assuming you have composer installed, simply run `composer install`.

## Local server
If you want to run a simple local server on your machine to review your contributions before merging them, create a copy of the `.env.dist` file called `.env`, then edit your personal `.env` file and update the configuration to say `SERVE_STATIC=true`.
Then run `php -S localhost:8080 public/index.php`, now you can visit `http://localhost:8080/` and play with the code, have fun! :-)

=======
[ico-travis]: https://img.shields.io/travis/PHPDX/cascadiaphp.com/develop.svg?style=flat-square
[ico-scrutinizer]: https://img.shields.io/scrutinizer/coverage/g/PHPDX/cascadiaphp.com/develop.svg?style=flat-square
[ico-code-quality]: https://img.shields.io/scrutinizer/g/PHPDX/cascadiaphp.com.svg?style=flat-square

[link-travis]: https://travis-ci.org/PHPDX/cascadiaphp.com
[link-scrutinizer]: https://scrutinizer-ci.com/g/PHPDX/cascadiaphp.com/code-structure
[link-code-quality]: https://scrutinizer-ci.com/g/PHPDX/cascadiaphp.com
