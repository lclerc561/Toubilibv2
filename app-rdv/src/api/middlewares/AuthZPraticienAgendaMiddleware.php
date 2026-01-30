<?php
namespace toubilib\api\middlewares;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Slim\Psr7\Response as SlimResponse;
use Slim\Routing\RouteContext;

/**
 * Middleware d'autorisation pour l'accès à l'agenda d'un praticien
 *
 * Vérifie que l'utilisateur authentifié est le praticien concerné par l'agenda demandé.
 * IMPORTANT: Ce middleware doit être placé APRÈS AuthNMiddleware qui valide le token.
 */
class AuthZPraticienAgendaMiddleware implements MiddlewareInterface
{
    public function process(Request $request, RequestHandler $handler): Response
    {
        // Récupérer les données utilisateur validées par AuthNMiddleware
        $user = $request->getAttribute('user');

        if (!$user) {
            return $this->forbidden("Authentification requise");
        }

        // Vérification du rôle Praticien (role = 10)
        if ($user['role'] !== 10) {
            return $this->forbidden("Accès réservé aux praticiens");
        }

        // Extraction de l'ID du praticien depuis la route
        $routeContext = RouteContext::fromRequest($request);
        $route = $routeContext->getRoute();
        $praticienId = $route->getArgument('id');

        if (!$praticienId) {
            $response = new SlimResponse();
            $response->getBody()->write(json_encode(['status' => 'error', 'message' => 'ID praticien manquant'], JSON_UNESCAPED_UNICODE));
            return $response->withStatus(400)->withHeader('Content-Type', 'application/json');
        }

        // Le praticien authentifié doit correspondre à l'ID de l'agenda demandé
        if ($user['id'] !== $praticienId) {
            return $this->forbidden("Accès non autorisé à cet agenda");
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
