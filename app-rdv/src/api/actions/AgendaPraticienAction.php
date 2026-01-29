<?php
namespace toubilib\api\actions;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use toubilib\core\application\usecases\ServiceRDVInterface;
use toubilib\api\services\HATEOASService;

class AgendaPraticienAction
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
        $praticienId = $args['id'] ?? null;
        if (!$praticienId) {
            $response->getBody()->write(json_encode([
                'status' => 'error',
                'message' => 'ID du praticien manquant'
            ], JSON_UNESCAPED_UNICODE));
            return $response->withStatus(400)->withHeader('Content-Type', 'application/json');
        }

        $query = $request->getQueryParams();
        
        try {
            $dateDebut = isset($query['dateDebut']) ? new \DateTime($query['dateDebut']) : null;
            $dateFin = isset($query['dateFin']) ? new \DateTime($query['dateFin']) : null;
            
            // Validation : date de début doit être antérieure à la date de fin si les deux sont fournies
            if ($dateDebut && $dateFin && $dateDebut > $dateFin) {
                $response->getBody()->write(json_encode([
                    'status' => 'error',
                    'message' => 'Date de début doit être antérieure à la date de fin'
                ], JSON_UNESCAPED_UNICODE));
                return $response->withStatus(400)->withHeader('Content-Type', 'application/json');
            }
            
        } catch (\Exception $e) {
            $response->getBody()->write(json_encode([
                'status' => 'error',
                'message' => 'Format de date invalide (YYYY-MM-DD)'
            ], JSON_UNESCAPED_UNICODE));
            return $response->withStatus(400)->withHeader('Content-Type', 'application/json');
        }

        $agenda = $this->serviceRDV->getAgendaPraticien($praticienId, $dateDebut, $dateFin);
        
        $responseData = [
            'status' => 'success',
            'data' => [
                'agenda' => $agenda
            ],
            '_links' => $this->hateoasService->getAgendaLinks($praticienId)
        ];

        $response->getBody()->write(json_encode($responseData, JSON_UNESCAPED_UNICODE));
        return $response->withStatus(200)->withHeader('Content-Type', 'application/json');
    }
}
