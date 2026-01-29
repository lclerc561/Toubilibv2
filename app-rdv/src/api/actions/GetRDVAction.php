<?php
namespace toubilib\api\actions;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use toubilib\core\application\usecases\ServiceRDVInterface;
use toubilib\api\services\HATEOASService;

class GetRDVAction
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
        $rdvId = $args['id'] ?? null;
        if (!$rdvId) {
            $response->getBody()->write(json_encode([
                'status' => 'error',
                'message' => 'ID manquant'
            ], JSON_UNESCAPED_UNICODE));
            return $response->withStatus(400)->withHeader('Content-Type', 'application/json');
        }

        $rdv = $this->serviceRDV->consulterRdv($rdvId);
        if (!$rdv) {
            $response->getBody()->write(json_encode([
                'status' => 'error',
                'message' => 'RDV non trouvé'
            ], JSON_UNESCAPED_UNICODE));
            return $response->withStatus(404)->withHeader('Content-Type', 'application/json');
        }

        // accès aux propriétés publiques du DTO
        $responseData = [
            'status' => 'success',
            'data' => [
                'id' => $rdv->id,
                'praticienId' => $rdv->praticienId,
                'patientId' => $rdv->patientId,
                'patientEmail' => $rdv->patientEmail,
                'dateHeureDebut' => $rdv->dateHeureDebut,
                'dateHeureFin' => $rdv->dateHeureFin,
                'status' => $rdv->status,
                'duree' => $rdv->duree,
                'dateCreation' => $rdv->dateCreation,
                'motifVisite' => $rdv->motifVisite
            ],
            '_links' => $this->hateoasService->getRDVLinks($rdv->id)
        ];

        $response->getBody()->write(json_encode($responseData, JSON_UNESCAPED_UNICODE));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
    }
}
