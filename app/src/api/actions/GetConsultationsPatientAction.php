<?php

namespace toubilib\api\actions;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use toubilib\core\application\usecases\ServiceRDVInterface;
use toubilib\core\application\usecases\ServicePraticienInterface;
use toubilib\api\services\HATEOASService;

class GetConsultationsPatientAction
{
    private ServiceRDVInterface $serviceRDV;
    private ServicePraticienInterface $servicePraticien;
    private HATEOASService $hateoasService;

    public function __construct(ServiceRDVInterface $serviceRDV,ServicePraticienInterface $servicePraticien,HATEOASService $hateoasService)
    {
        $this->serviceRDV = $serviceRDV;
        $this->servicePraticien = $servicePraticien;
        $this->hateoasService = $hateoasService;
    }

    public function __invoke(Request $request, Response $response, array $args): Response
    {
        $patientId = $args['id'] ?? null;
        if (!$patientId) {
            $response->getBody()->write(json_encode(['error' => 'ID manquant'], JSON_UNESCAPED_UNICODE));
            return $response->withStatus(400)->withHeader('Content-Type', 'application/json');
        }

        $consultations = $this->serviceRDV->listerConsultationsPatient($patientId);

        $data = [];
        foreach ($consultations as $consultation) {
            $praticien = $this->servicePraticien->RecherchePraticienByID($consultation->praticienId);
            
            // Gérer le cas où le praticien n'existe plus (données orphelines)
            if ($praticien === null) {
                $praticienData = [
                    'id' => $consultation->praticienId,
                    'nom' => 'Non disponible',
                    'prenom' => 'Non disponible',
                    'specialite' => 'Non disponible'
                ];
            } else {
                $praticienData = [
                    'id' => $praticien->id,
                    'nom' => $praticien->nom,
                    'prenom' => $praticien->prenom,
                    'specialite' => $praticien->specialite
                ];
            }
            
            $data[] = [
                'id' => $consultation->id,
                'praticien' => $praticienData,
                'dateHeureDebut' => $consultation->dateHeureDebut,
                'dateHeureFin' => $consultation->dateHeureFin,
                'motifVisite' => $consultation->motifVisite,
                'duree' => $consultation->duree,
                'status' => $consultation->status,
                'dateCreation' => $consultation->dateCreation,
                '_links' => $this->hateoasService->getRDVLinks($consultation->id)
            ];
        }
        
        $responseData = [
            'status' => 'success',
            'data' => $data,
            '_links' => $this->hateoasService->getPatientLinks($patientId)
        ];

        $response->getBody()->write(json_encode($responseData, JSON_UNESCAPED_UNICODE));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
    }
}
