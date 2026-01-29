<?php
namespace toubilib\api\middlewares;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;

class CORSMiddleware implements MiddlewareInterface
{
    public function process(Request $request, RequestHandler $handler): Response
    {
        $response = $handler->handle($request);

        // Headers CORS pour permettre l'accès depuis n'importe quelle origine
        $response = $response->withHeader('Access-Control-Allow-Origin', '*');
        $response = $response->withHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS');
        $response = $response->withHeader('Access-Control-Allow-Headers', 'Content-Type, Authorization, X-Requested-With');
        $response = $response->withHeader('Access-Control-Max-Age', '86400'); // 24 heures

        // Gérer les requêtes OPTIONS (preflight)
        if ($request->getMethod() === 'OPTIONS') {
            $response = $response->withStatus(200);
            $response->getBody()->write('');
        }

        return $response;
    }
}
