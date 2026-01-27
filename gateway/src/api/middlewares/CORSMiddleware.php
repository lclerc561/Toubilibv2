<?php
namespace toubilib\gateway\api\middlewares;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;

class CORSMiddleware implements MiddlewareInterface
{
    public function process(Request $request, RequestHandler $handler): Response
    {
        // Gérer les requêtes OPTIONS (preflight) avant d'appeler le handler
        if ($request->getMethod() === 'OPTIONS') {
            $response = new \Slim\Psr7\Response();
            $response = $response->withHeader('Access-Control-Allow-Origin', '*');
            $response = $response->withHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, PATCH, OPTIONS');
            $response = $response->withHeader('Access-Control-Allow-Headers', 'Content-Type, Authorization, X-Requested-With');
            $response = $response->withHeader('Access-Control-Max-Age', '86400'); // 24 heures
            return $response->withStatus(200);
        }

        // Pour les autres requêtes, appeler le handler puis ajouter les headers CORS
        $response = $handler->handle($request);

        // Headers CORS pour permettre l'accès depuis n'importe quelle origine
        $response = $response->withHeader('Access-Control-Allow-Origin', '*');
        $response = $response->withHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, PATCH, OPTIONS');
        $response = $response->withHeader('Access-Control-Allow-Headers', 'Content-Type, Authorization, X-Requested-With');
        $response = $response->withHeader('Access-Control-Max-Age', '86400'); // 24 heures

        return $response;
    }
}