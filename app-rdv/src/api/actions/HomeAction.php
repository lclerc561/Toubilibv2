<?php

namespace toubilib\api\actions;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

class HomeAction
{
    public function __invoke(Request $request, Response $response): Response
    {
        $response->getBody()->write(json_encode([
            'message' => "API RDV - Microservice",
            'endpoints' => [
                "GET /" => "Cette page",
                "GET /rdvs/{id}" => "Consulter un rendez-vous",
                "POST /rdvs" => "Créer un rendez-vous",
                "DELETE /rdvs/{id}/annuler" => "Annuler un rendez-vous",
                "PATCH /rdvs/{id}/honorer" => "Marquer un RDV comme honoré",
                "PATCH /rdvs/{id}/non-honorer" => "Marquer un RDV comme non honoré",
                "GET /praticiens/{id}/rdvs/occupes" => "Lister les créneaux occupés d'un praticien",
                "GET /praticiens/{id}/agenda" => "Consulter l'agenda d'un praticien",
                "GET /patients/{id}" => "Consulter un patient",
                "GET /patients/{id}/consultations" => "Lister les consultations d'un patient"
            ]
        ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
        
        return $response
            ->withHeader('Content-Type', 'application/json')
            ->withStatus(200);
    }
}