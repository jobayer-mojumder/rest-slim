<?php
class CorsMiddleware
{
private $router;

public function __construct(\Slim\Router $router)
{
    $this->router = $router;
}
/**
 * Cors middleware invokable class
 *
 * @param  \Psr\Http\Message\ServerRequestInterface $request  PSR7 request
 * @param  \Psr\Http\Message\ResponseInterface      $response PSR7 response
 * @param  callable                                 $next     Next middleware
 *
 * @return \Psr\Http\Message\ResponseInterface
 */
public function __invoke($request, $response, $next)
{
    // https://www.html5rocks.com/static/images/cors_server_flowchart.png
    if ($request->isOptions()
          && $request->hasHeader('Origin')
          && $request->hasHeader('Access-Control-Request-Method')) {
        return $response
                      ->withHeader('Access-Control-Allow-Origin', '*')
                      ->withHeader('Access-Control-Allow-Headers', '*')
                      ->withHeader("Access-Control-Allow-Methods", '*');
    } else {
        $response = $response
                      ->withHeader('Access-Control-Allow-Origin', '*')
                      ->withHeader('Access-Control-Expose-Headers', '*');
        return $next($request, $response);
    }
}
}