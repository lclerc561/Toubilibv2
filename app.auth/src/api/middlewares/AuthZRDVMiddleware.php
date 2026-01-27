<?php
namespace toubilib\api\middlewares;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use toubilib\core\application\usecases\ServiceRDVInterface;

class AuthZRDVMiddleware implements MiddlewareInterface
{
    private ServiceRDVInterface $serviceRDV;

    public function __construct(ServiceRDVInterface $serviceRDV)
    {
        $this->serviceRDV = $serviceRDV;
    }

    public function process(Request $request, RequestHandler $handler): Response
    {
        $user = $request->getAttribute('user');
        
        // Extraire l'ID du RDV depuis l'URI
        $path = $request->getUri()->getPath();
        $pathParts = explode('/', trim($path, '/'));
        $rdvId = null;
        
        // Chercher l'ID après '/rdvs/'
        for ($i = 0; $i < count($pathParts) - 1; $i++) {
            if ($pathParts[$i] === 'rdvs' && isset($pathParts[$i + 1])) {
                $rdvId = $pathParts[$i + 1];
                break;
            }
        }

        if (!$user || !$rdvId) {
            $response = new \Slim\Psr7\Response();
            $response->getBody()->write(json_encode([
                'status' => 'error',
                'message' => 'Paramètres manquants'
            ], JSON_UNESCAPED_UNICODE));
            return $response->withStatus(400)->withHeader('Content-Type', 'application/json');
        }

        try {
            // Récupérer les informations du RDV
            $rdv = $this->serviceRDV->consulterRdv($rdvId);
            if (!$rdv) {
                $response = new \Slim\Psr7\Response();
                $response->getBody()->write(json_encode([
                    'status' => 'error',
                    'message' => 'RDV non trouvé'
                ], JSON_UNESCAPED_UNICODE));
                return $response->withStatus(404)->withHeader('Content-Type', 'application/json');
            }

            // Vérifier que l'utilisateur est soit le patient soit le praticien du RDV
            $isPatient = $user['role'] === 1 && $user['id'] === $rdv->patientId;
            $isPraticien = $user['role'] === 10 && $user['id'] === $rdv->praticienId;

            if (!$isPatient && !$isPraticien) {
                $response = new \Slim\Psr7\Response();
                $response->getBody()->write(json_encode([
                    'status' => 'error',
                    'message' => 'Accès non autorisé à ce RDV'
                ], JSON_UNESCAPED_UNICODE));
                return $response->withStatus(403)->withHeader('Content-Type', 'application/json');
            }

            return $handler->handle($request);
        } catch (\Exception $e) {
            $response = new \Slim\Psr7\Response();
            $response->getBody()->write(json_encode([
                'status' => 'error',
                'message' => 'Erreur lors de la vérification des permissions'
            ], JSON_UNESCAPED_UNICODE));
            return $response->withStatus(500)->withHeader('Content-Type', 'application/json');
        }
    }
}
