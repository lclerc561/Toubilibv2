<?php

namespace toubilib\api\actions;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use toubilib\core\application\usecases\ServiceIndisponibiliteInterface;
use toubilib\api\services\HATEOASService;

class CreateIndisponibiliteAction
{
    private ServiceIndisponibiliteInterface $serviceIndisponibilite;
    private HATEOASService $hateoasService;

    public function __construct(
        ServiceIndisponibiliteInterface $serviceIndisponibilite,
        HATEOASService $hateoasService
    ) {
        $this->serviceIndisponibilite = $serviceIndisponibilite;
        $this->hateoasService = $hateoasService;
    }

    public function __invoke(Request $request, Response $response, array $args): Response
    {
        $dto = $request->getAttribute('inputIndisponibiliteDto');
        if (!$dto) {
            $response->getBody()->write(json_encode([
                'status' => 'error',
                'message' => 'Erreur de configuration serveur'
            ], JSON_UNESCAPED_UNICODE));
            return $response->withStatus(500)->withHeader('Content-Type', 'application/json');
        }

        try {
            $indisponibiliteDTO = $this->serviceIndisponibilite->creerIndisponibilite($dto);

            $responseData = [
                'status' => 'success',
                'data' => [
                    'message' => 'Indisponibilité créée avec succès',
                    'id' => $indisponibiliteDTO->id,
                    'praticienId' => $indisponibiliteDTO->praticienId,
                    'dateDebut' => $indisponibiliteDTO->dateDebut,
                    'dateFin' => $indisponibiliteDTO->dateFin,
                    'raison' => $indisponibiliteDTO->raison,
                    'dateCreation' => $indisponibiliteDTO->dateCreation
                ],
                '_links' => [
                    'self' => [
                        'href' => "{$this->hateoasService->getBaseUrl()}/praticiens/{$indisponibiliteDTO->praticienId}/indisponibilites/{$indisponibiliteDTO->id}",
                        'method' => 'GET'
                    ],
                    'praticien' => [
                        'href' => "{$this->hateoasService->getBaseUrl()}/praticiens/{$indisponibiliteDTO->praticienId}",
                        'method' => 'GET'
                    ],
                    'list' => [
                        'href' => "{$this->hateoasService->getBaseUrl()}/praticiens/{$indisponibiliteDTO->praticienId}/indisponibilites",
                        'method' => 'GET'
                    ]
                ]
            ];

            $response->getBody()->write(json_encode($responseData, JSON_UNESCAPED_UNICODE));
            return $response
                ->withHeader('Content-Type', 'application/json')
                ->withStatus(201);
        } catch (\Exception $e) {
            $statusCode = 400;
            if (strpos($e->getMessage(), 'inexistant') !== false) {
                $statusCode = 404;
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

