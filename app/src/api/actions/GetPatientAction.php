<?php
namespace toubilib\api\actions;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use toubilib\core\application\usecases\ServicePatient;
use toubilib\api\services\HATEOASService;

class GetPatientAction
{
    private ServicePatient $servicePatient;
    private HATEOASService $hateoasService;

    public function __construct(ServicePatient $servicePatient, HATEOASService $hateoasService)
    {
        $this->servicePatient = $servicePatient;
        $this->hateoasService = $hateoasService;
    }

    public function __invoke(Request $request, Response $response, array $args): Response
    {
        $patientId = $args['id'] ?? null;
        if (!$patientId) {
            $response->getBody()->write(json_encode([
                'status' => 'error',
                'message' => 'ID manquant'
            ], JSON_UNESCAPED_UNICODE));
            return $response->withStatus(400)->withHeader('Content-Type', 'application/json');
        }

        $patient = $this->servicePatient->consulterPatient($patientId);
        if (!$patient) {
            $response->getBody()->write(json_encode([
                'status' => 'error',
                'message' => 'Patient non trouvé'
            ], JSON_UNESCAPED_UNICODE));
            return $response->withStatus(404)->withHeader('Content-Type', 'application/json');
        }

        $responseData = [
            'status' => 'success',
            'data' => [
                'id' => $patient->getId(),
                'nom' => $patient->getNom(),
                'prenom' => $patient->getPrenom(),
                'dateNaissance' => $patient->getDateNaissance()?->format('Y-m-d'),
                'adresse' => $patient->getAdresse(),
                'codePostal' => $patient->getCodePostal(),
                'ville' => $patient->getVille(),
                'email' => $patient->getEmail(),
                'telephone' => $patient->getTelephone()
            ],
            '_links' => $this->hateoasService->getPatientLinks($patientId)
        ];
        
        $response->getBody()->write(json_encode($responseData, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));

        return $response->withStatus(200)->withHeader('Content-Type', 'application/json');
    }
}
