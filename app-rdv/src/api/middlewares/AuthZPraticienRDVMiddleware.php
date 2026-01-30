<?php
namespace toubilib\api\middlewares;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use toubilib\core\application\usecases\ServiceRDVInterface;
use Slim\Psr7\Response as SlimResponse;
use Slim\Routing\RouteContext;

/**
 * Middleware d'autorisation pour les actions praticien sur les RDV
 *
 * Vérifie que l'utilisateur authentifié est le praticien concerné par le RDV.
 * IMPORTANT: Ce middleware doit être placé APRÈS AuthNMiddleware qui valide le token.
 */
class AuthZPraticienRDVMiddleware implements MiddlewareInterface
{
    private ServiceRDVInterface $serviceRDV;

    public function __construct(ServiceRDVInterface $serviceRDV)
    {
        $this->serviceRDV = $serviceRDV;
    }

    public function process(Request $request, RequestHandler $handler): Response
    {
        // Récupérer les données utilisateur validées par AuthNMiddleware
        $user = $request->getAttribute('user');

        if (!$user) {
            return $this->forbidden("Authentification requise", 401);
        }

        // Vérification du rôle Praticien (role = 10)
        if ($user['role'] !== 10) {
            return $this->forbidden("Accès réservé aux praticiens");
        }

        // Vérification de la propriété du RDV
        $routeContext = RouteContext::fromRequest($request);
        $rdvId = $routeContext->getRoute()->getArgument('id');

        try {
            // On récupère le RDV pour comparer l'ID Praticien
            $rdv = $this->serviceRDV->consulterRdv($rdvId);

            if (!$rdv) {
                return $this->forbidden("Rendez-vous inexistant", 404);
            }

            if ($user['id'] !== $rdv->praticienId) {
                return $this->forbidden("Vous n'êtes pas le praticien en charge de ce rendez-vous");
            }

            return $handler->handle($request);
        } catch (\Exception $e) {
            return $this->forbidden("Erreur d'autorisation", 500);
        }
    }

    private function forbidden(string $msg, int $status = 403): Response
    {
        $resp = new SlimResponse();
        $resp->getBody()->write(json_encode(['status' => 'error', 'message' => $msg]));
        return $resp->withStatus($status)->withHeader('Content-Type', 'application/json');
    }
}
