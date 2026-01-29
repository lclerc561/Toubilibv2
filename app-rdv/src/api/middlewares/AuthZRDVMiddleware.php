<?php
namespace toubilib\api\middlewares;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use toubilib\core\application\usecases\ServiceRDVInterface;
use Slim\Psr7\Response as SlimResponse;
use Slim\Routing\RouteContext;

class AuthZRDVMiddleware implements MiddlewareInterface
{
    private ServiceRDVInterface $serviceRDV;

    public function __construct(ServiceRDVInterface $serviceRDV)
    {
        $this->serviceRDV = $serviceRDV;
    }

    public function process(Request $request, RequestHandler $handler): Response
    {
        // 1. Extraction et Décodage du JWT (Spécifique Microservice)
        $authHeader = $request->getHeaderLine('Authorization');
        $token = str_replace('Bearer ', '', $authHeader);
        $tokenParts = explode('.', $token);

        if (count($tokenParts) !== 3) {
            return $this->errorResponse("Jeton JWT invalide ou manquant", 401);
        }

        // Décodage du payload (segment du milieu)
        $user = json_decode(base64_decode($tokenParts[1]), true);
        if (!$user) {
            return $this->errorResponse("Impossible de décoder l'utilisateur", 401);
        }

        // 2. Extraction de l'ID du RDV depuis la route Slim
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

            // 3. Contrôle d'autorisation (Exercice 5)
            // Patient (role 1) ou Praticien (role 10)
            $isPatientOwner = ($user['role'] === 1 && $user['id'] === $rdv->patientId);
            $isPraticienOwner = ($user['role'] === 10 && $user['id'] === $rdv->praticienId);

            if (!$isPatientOwner && !$isPraticienOwner) {
                return $this->errorResponse("Accès interdit : vous n'êtes pas lié à ce RDV", 403);
            }

            return $handler->handle($request);

        } catch (\Exception $e) {
            return $this->errorResponse("Erreur lors de la vérification : " . $e->getMessage(), 403);
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