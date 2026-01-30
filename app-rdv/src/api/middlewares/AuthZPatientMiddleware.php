<?php
namespace toubilib\api\middlewares;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Slim\Psr7\Response as SlimResponse;
use Slim\Routing\RouteContext;

class AuthZPatientMiddleware implements MiddlewareInterface
{
    public function process(Request $request, RequestHandler $handler): Response
    {
        // 1. Extraction et décodage du JWT
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

        //Vérification du rôle Patient
        if ($user['role'] !== 1) {
            return $this->forbidden("Accès réservé aux patients");
        }

        //Vérification de l'ID
        $routeContext = RouteContext::fromRequest($request);
        $route = $routeContext->getRoute();
        $targetId = $route->getArgument('id');
        
        //Si un ID est dans l'URL,il doit correspondre à celui du token
        if ($targetId && $user['id'] !== $targetId) {
            return $this->forbidden("Vous n'avez pas l'autorisation d'accéder à ces données");
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