<?php

namespace toubilib\api\actions;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use toubilib\core\application\usecases\ServicePraticienInterface;
use toubilib\api\services\HATEOASService;

class RecherchePraticiensSpeVilleAction
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
        $params = $request->getQueryParams();
        $specialite = $params['specialite'] ?? null;
        $ville = $params['ville'] ?? null;

        $praticiens = $this->servicePraticien->rechercherPraticiensSpeVille($specialite, $ville);

        $out = [];
        foreach ($praticiens as $p) {
            $out[] = [
                'id' => $p->id,
                'nom' => $p->nom,
                'prenom' => $p->prenom,
                'ville' => $p->ville,
                'email' => $p->email,
                'telephone' => $p->telephone,
                'specialite' => $p->specialite,
                'structureNom' => $p->structureNom,
                'adresse' => $p->adresse,
                'codePostal' => $p->codePostal,
                'structureVille' => $p->structureVille,
                'rppsId' => $p->rppsId,
                'titre' => $p->titre,
                'accepteNouveauPatient' => $p->accepteNouveauPatient,
                'estOrganisation' => $p->estOrganisation,
                'motifsVisite' => $this->servicePraticien->getMotifsVisite($p->id),
                'moyensPaiement' => $this->servicePraticien->getMoyensPaiement($p->id),
                '_links' => $this->hateoasService->getPraticienLinks($p->id)
            ];
        }

        $responseData = [
            'status' => 'success',
            'data' => $out,
            '_links' => $this->hateoasService->getPraticiensListLinks()
        ];
        
        $response->getBody()->write(json_encode($responseData, JSON_UNESCAPED_UNICODE));

        return $response
            ->withHeader('Content-Type', 'application/json')
            ->withStatus(200);
    }
}
