<?php

namespace toubilib\api\actions;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use toubilib\core\application\usecases\ServiceAuthInterface;
use toubilib\api\services\HATEOASService;
use toubilib\core\application\dto\InputRegisterPatientDTO;

class RegisterPatientAction
{
    private ServiceAuthInterface $serviceAuth;
    private HATEOASService $hateoasService;

    public function __construct(ServiceAuthInterface $serviceAuth, HATEOASService $hateoasService)
    {
        $this->serviceAuth = $serviceAuth;
        $this->hateoasService = $hateoasService;
    }

    public function __invoke(Request $request, Response $response, array $args): Response
    {
        $dto = $request->getAttribute('inputRegisterPatientDto');
        if (!$dto) {
            $response->getBody()->write(json_encode([
                'status' => 'error',
                'message' => 'Erreur de configuration serveur'
            ], JSON_UNESCAPED_UNICODE));
            return $response->withStatus(500)->withHeader('Content-Type', 'application/json');
        }

        try {
            $authDTO = $this->serviceAuth->inscrirePatient($dto);

            $responseData = [
                'status' => 'success',
                'data' => [
                    'message' => 'Patient inscrit avec succès',
                    'id' => $authDTO->id,
                    'email' => $authDTO->email,
                    'nom' => $dto->nom,
                    'prenom' => $dto->prenom
                ],
                '_links' => array_merge(
                    $this->hateoasService->getPatientLinks($authDTO->id),
                    [
                        'login' => [
                            'href' => "{$this->hateoasService->getBaseUrl()}/auth/login",
                            'method' => 'POST',
                            'description' => 'Se connecter'
                        ]
                    ]
                )
            ];

            $response->getBody()->write(json_encode($responseData, JSON_UNESCAPED_UNICODE));
            return $response
                ->withHeader('Content-Type', 'application/json')
                ->withStatus(201);
        } catch (\Exception $e) {
            $statusCode = 409; // Conflict par défaut
            if (strpos($e->getMessage(), 'déjà utilisé') !== false) {
                $statusCode = 409;
            } elseif (strpos($e->getMessage(), 'Erreur') !== false) {
                $statusCode = 500;
            }

            $response->getBody()->write(json_encode([
                'status' => 'error',
                'message' => $e->getMessage()
            ], JSON_UNESCAPED_UNICODE));
            return $response
                ->withHeader('Content-Type', 'application/json')
                ->withStatus($statusCode);
        }
    }
}

