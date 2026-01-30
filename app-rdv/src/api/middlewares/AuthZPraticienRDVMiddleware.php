<?php
namespace toubilib\api\middlewares;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use toubilib\core\application\usecases\ServiceRDVInterface;
use Slim\Psr7\Response as SlimResponse;
use Slim\Routing\RouteContext;

class AuthZPraticienRDVMiddleware implements MiddlewareInterface
{
    private ServiceRDVInterface $serviceRDV;

    public function __construct(ServiceRDVInterface $serviceRDV) {
        $this->serviceRDV = $serviceRDV;
    }

    public function process(Request $request, RequestHandler $handler): Response
    {
        $authHeader = $request->getHeaderLine('Authorization');
        $tokenParts = explode('.', str_replace('Bearer ', '', $authHeader));
        
        if (count($tokenParts) !== 3) {
            return $this->forbidden("Jeton JWT invalide");
        }

        $payload = json_decode(base64_decode($tokenParts[1]), true);
        
        if (!isset($payload['data'])) {
            return $this->forbidden("Données utilisateur introuvables");
        }
        
        $user = $payload['data'];

        //Vérification du rôle Praticien
        if ($user['role'] !== 10) {
            return $this->forbidden("Accès réservé aux praticiens");
        }

        //Vérification de la propriété du RDV
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
            return $this->forbidden("Erreur d'autorisation : " . $e->getMessage());
        }
    }

    private function forbidden(string $msg, int $status = 403): Response {
        $resp = new SlimResponse();
        $resp->getBody()->write(json_encode(['status' => 'error', 'message' => $msg]));
        return $resp->withStatus($status)->withHeader('Content-Type', 'application/json');
    }
}