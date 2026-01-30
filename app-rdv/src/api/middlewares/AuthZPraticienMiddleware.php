<?php
namespace toubilib\api\middlewares;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Slim\Psr7\Response as SlimResponse;
use Slim\Routing\RouteContext;

class AuthZPraticienMiddleware implements MiddlewareInterface
{
    public function process(Request $request, RequestHandler $handler): Response
    {
        $authHeader = $request->getHeaderLine('Authorization');
        $tokenParts = explode('.', str_replace('Bearer ', '', $authHeader));
        
        if (count($tokenParts) !== 3) {
            return $this->forbidden("Jeton JWT invalide ou manquant");
        }

        $payload = json_decode(base64_decode($tokenParts[1]), true);

        if (!isset($payload['data'])) {
            return $this->forbidden("Données utilisateur manquantes dans le jeton");
        }

        $user = $payload['data'];

        //Vérification du rôle Praticien
        if ($user['role'] !== 10) {
            return $this->forbidden("Accès réservé aux praticiens");
        }

        //Vérification de l'ID Praticien si présent dans la route
        $routeContext = RouteContext::fromRequest($request);
        $route = $routeContext->getRoute();
        $praticienId = $route->getArgument('id');
        
        //Si l'URL contient un ID, il doit correspondre à l'ID du token
        if ($praticienId && $user['id'] !== $praticienId) {
            return $this->forbidden("Accès refusé : vous n'êtes pas le propriétaire de cette ressource");
        }

        return $handler->handle($request);
    }

    private function forbidden(string $message): Response
    {
        $response = new SlimResponse();
        $response->getBody()->write(json_encode([
            'status' => 'error',
            'message' => $message
        ], JSON_UNESCAPED_UNICODE));
        return $response->withStatus(403)->withHeader('Content-Type', 'application/json');
    }
}