<?php
namespace toubilib\api\actions;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use toubilib\core\application\usecases\ServiceRDVInterface;
use toubilib\api\services\HATEOASService;
use toubilib\api\handlers\ExceptionHandler;
use Slim\Psr7\Response as SlimResponse;

class CreateRDVAction
{
    private ServiceRDVInterface $serviceRDV;
    private HATEOASService $hateoasService;

    public function __construct(ServiceRDVInterface $serviceRDV, HATEOASService $hateoasService)
    {
        $this->serviceRDV = $serviceRDV;
        $this->hateoasService = $hateoasService;
    }

    public function __invoke(Request $request, Response $response, array $args): Response
    {
        $dto = $request->getAttribute('inputRdvDto');
        if (! $dto) {
            $res = new SlimResponse();
            $res->getBody()->write(json_encode([
                'status' => 'error',
                'message' => 'Input DTO manquant (middleware absent?)'
            ], JSON_UNESCAPED_UNICODE));
            return $res->withHeader('Content-Type', 'application/json')->withStatus(500);
        }

        try {
            $rdv = $this->serviceRDV->creerRendezVous($dto);

            $responseData = [
                'status' => 'success',
                'data' => [
                    'id' => $rdv->getId(),
                    'praticienId' => $rdv->getPraticienId(),
                    'patientId' => $rdv->getPatientId(),
                    'patientEmail' => $rdv->getPatientEmail(),
                    'dateHeureDebut' => $rdv->getDateHeureDebut()->format('Y-m-d H:i:s'),
                    'dateHeureFin' => $rdv->getDateHeureFin() ? $rdv->getDateHeureFin()->format('Y-m-d H:i:s') : null,
                    'duree' => $rdv->getDuree(),
                    'motifVisite' => $rdv->getMotifVisite(),
                    'dateCreation' => $rdv->getDateCreation() ? $rdv->getDateCreation()->format('Y-m-d H:i:s') : null,
                    'status' => $rdv->getStatus()
                ],
                '_links' => $this->hateoasService->getRDVLinks($rdv->getId())
            ];

            $res = new SlimResponse();
            $res->getBody()->write(json_encode($responseData, JSON_UNESCAPED_UNICODE));
            return $res->withHeader('Content-Type', 'application/json')->withStatus(201);
        } catch (\Exception $e) {
            // Utiliser le gestionnaire centralisé d'exceptions
            $errorInfo = ExceptionHandler::handle($e);

            // Logger les erreurs serveur (5xx) mais pas les erreurs client (4xx)
            if (ExceptionHandler::shouldLog($e)) {
                error_log("CreateRDVAction - Erreur: " . get_class($e) . " - " . $e->getMessage());
            }

            $res = new SlimResponse();
            $res->getBody()->write(json_encode([
                'status' => 'error',
                'message' => $errorInfo['message']
            ], JSON_UNESCAPED_UNICODE));
            return $res->withHeader('Content-Type', 'application/json')->withStatus($errorInfo['status']);
        }
    }
}