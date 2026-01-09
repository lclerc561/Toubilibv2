<?php

namespace toubilib\api\actions;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use toubilib\core\application\usecases\ServiceIndisponibiliteInterface;
use toubilib\api\services\HATEOASService;

class ListIndisponibilitesAction
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
        $praticienId = $args['id'] ?? null;
        if (!$praticienId) {
            $response->getBody()->write(json_encode([
                'status' => 'error',
                'message' => 'ID praticien manquant'
            ], JSON_UNESCAPED_UNICODE));
            return $response->withStatus(400)->withHeader('Content-Type', 'application/json');
        }

        $indisponibilites = $this->serviceIndisponibilite->listerIndisponibilites($praticienId);

        $data = [];
        foreach ($indisponibilites as $indisponibilite) {
            $data[] = [
                'id' => $indisponibilite->id,
                'dateDebut' => $indisponibilite->dateDebut,
                'dateFin' => $indisponibilite->dateFin,
                'raison' => $indisponibilite->raison,
                'dateCreation' => $indisponibilite->dateCreation,
                '_links' => [
                    'self' => [
                        'href' => "{$this->hateoasService->getBaseUrl()}/praticiens/{$praticienId}/indisponibilites/{$indisponibilite->id}",
                        'method' => 'DELETE'
                    ]
                ]
            ];
        }

        $responseData = [
            'status' => 'success',
            'data' => $data,
            '_links' => [
                'praticien' => [
                    'href' => "{$this->hateoasService->getBaseUrl()}/praticiens/{$praticienId}",
                    'method' => 'GET'
                ],
                'agenda' => [
                    'href' => "{$this->hateoasService->getBaseUrl()}/praticiens/{$praticienId}/agenda",
                    'method' => 'GET'
                ]
            ]
        ];

        $response->getBody()->write(json_encode($responseData, JSON_UNESCAPED_UNICODE));
        return $response
            ->withHeader('Content-Type', 'application/json')
            ->withStatus(200);
    }
}

