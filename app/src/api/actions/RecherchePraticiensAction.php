<?php

namespace toubilib\api\actions;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use toubilib\core\application\usecases\ServicePraticienInterface;
use toubilib\api\services\HATEOASService;

class RecherchePraticiensAction
{
    private ServicePraticienInterface $servicePraticien;
    private HATEOASService $hateoasService;

    public function __construct(ServicePraticienInterface $servicePraticien, HATEOASService $hateoasService)
    {
        $this->servicePraticien = $servicePraticien;
        $this->hateoasService = $hateoasService;
    }
    
    public function __invoke(Request $request, Response $response, array $args): Response
    {
        $id = $args['id'] ?? null;

        if ($id === null) {
            $response->getBody()->write(json_encode([
                'status' => 'error',
                'message' => 'ID du praticien requis'
            ], JSON_UNESCAPED_UNICODE));

            return $response
                ->withHeader('Content-Type', 'application/json')
                ->withStatus(400);
        }

        $praticien = $this->servicePraticien->RecherchePraticienByID($id);
        
        if ($praticien === null) {
            $response->getBody()->write(json_encode([
                'status' => 'error',
                'message' => 'Praticien non trouvé'
            ], JSON_UNESCAPED_UNICODE));
            return $response
                ->withHeader('Content-Type', 'application/json')
                ->withStatus(404);
        }
        
        $responseData = [
            'status' => 'success',
            'data' => [
                'id' => $praticien->id,
                'nom' => $praticien->nom,
                'prenom' => $praticien->prenom,
                'ville' => $praticien->ville,
                'email' => $praticien->email,
                'telephone' => $praticien->telephone,
                'specialite' => $praticien->specialite,
                'structureNom' => $praticien->structureNom,
                'adresse' => $praticien->adresse,
                'codePostal' => $praticien->codePostal,
                'structureVille' => $praticien->structureVille,
                'rppsId' => $praticien->rppsId,
                'titre' => $praticien->titre,
                'accepteNouveauPatient' => $praticien->accepteNouveauPatient,
                'estOrganisation' => $praticien->estOrganisation,
                'motifsVisite' => $praticien->motifsVisite,
                'moyensPaiement' => $praticien->moyensPaiement,
                '_links' => $this->hateoasService->getPraticienLinks($praticien->id)
            ]
        ];
        
        $response->getBody()->write(json_encode($responseData, JSON_UNESCAPED_UNICODE));
        return $response
            ->withHeader('Content-Type', 'application/json')
            ->withStatus(200);
    }
}