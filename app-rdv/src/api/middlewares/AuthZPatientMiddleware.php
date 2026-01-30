<?php
namespace toubilib\api\middlewares;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Slim\Psr7\Response as SlimResponse;
use Slim\Routing\RouteContext;

/**
 * Middleware d'autorisation pour les patients
 *
 * Vérifie que l'utilisateur authentifié est un patient et qu'il accède à ses propres données.
 * IMPORTANT: Ce middleware doit être placé APRÈS AuthNMiddleware qui valide le token.
 */
class AuthZPatientMiddleware implements MiddlewareInterface
{
    public function process(Request $request, RequestHandler $handler): Response
    {
        // Récupérer les données utilisateur validées par AuthNMiddleware
        $user = $request->getAttribute('user');

        if (!$user) {
            return $this->forbidden("Authentification requise");
        }

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