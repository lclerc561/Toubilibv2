<?php

namespace toubilib\api\actions;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use toubilib\core\application\usecases\ServicePraticienInterface;
use toubilib\api\services\HATEOASService;

class ListPraticiensAction
{
    private ServicePraticienInterface $servicePraticien;
    private HATEOASService $hateoasService;

    public function __construct(ServicePraticienInterface $servicePraticien, HATEOASService $hateoasService)
    {
        $this->servicePraticien = $servicePraticien;
        $this->hateoasService = $hateoasService;
    }

    public function __invoke(Request $request, Response $response): Response
    {
        $praticiens = $this->servicePraticien->listerPraticiens();
        $data = [];
        foreach ($praticiens as $praticien) {
            $data[] = [
                'nom' => $praticien->nom,
                'prenom' => $praticien->prenom,
                'ville' => $praticien->ville,
                'email' => $praticien->email,
                'specialite' => $praticien->specialite,
                '_links' => $this->hateoasService->getPraticienLinks($praticien->id)
            ];
        }

        $responseData = [
            'status' => 'success',
            'data' => [
                'praticiens' => $data
            ],
            '_links' => $this->hateoasService->getPraticiensListLinks()
        ];

        $response->getBody()->write(json_encode($responseData, JSON_UNESCAPED_UNICODE));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
    }
}

