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
 * Middleware d'autorisation pour l'accès aux RDV
 *
 * Vérifie que l'utilisateur authentifié (patient ou praticien) a le droit d'accéder au RDV demandé.
 * IMPORTANT: Ce middleware doit être placé APRÈS AuthNMiddleware qui valide le token.
 */
class AuthZRDVMiddleware implements MiddlewareInterface
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
            return $this->errorResponse("Authentification requise", 401);
        }

        // Extraction de l'ID du RDV depuis la route Slim
        $routeContext = RouteContext::fromRequest($request);
        $route = $routeContext->getRoute();
        $rdvId = $route->getArgument('id');

        if (!$rdvId) {
            return $this->errorResponse("ID du rendez-vous manquant", 400);
        }

        try {
            $rdv = $this->serviceRDV->consulterRdv($rdvId);
            if (!$rdv) {
                return $this->errorResponse("RDV non trouvé", 404);
            }

            // Contrôle d'autorisation
            // Patient (role 1) ou Praticien (role 10) concerné par le RDV
            $isPatientOwner = ($user['role'] === 1 && $user['id'] === $rdv->patientId);
            $isPraticienOwner = ($user['role'] === 10 && $user['id'] === $rdv->praticienId);

            if (!$isPatientOwner && !$isPraticienOwner) {
                return $this->errorResponse("Accès interdit : vous n'êtes pas lié à ce RDV", 403);
            }

            return $handler->handle($request);

        } catch (\Exception $e) {
            return $this->errorResponse("Erreur lors de la vérification : " . $e->getMessage(), 500);
        }
    }

    private function errorResponse(string $message, int $status): Response
    {
        $response = new SlimResponse();
        $response->getBody()->write(json_encode([
            'status' => 'error',
            'message' => $message
        ], JSON_UNESCAPED_UNICODE));
        return $response->withStatus($status)->withHeader('Content-Type', 'application/json');
    }
}
