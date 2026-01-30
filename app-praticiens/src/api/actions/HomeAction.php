<?php

namespace toubilib\api\actions;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

class HomeAction
{
    public function __invoke(Request $request, Response $response): Response
    {
        $response->getBody()->write(json_encode([
            'message' => "API Praticiens - Microservice",
            'endpoints' => [
                "GET /" => "Cette page",
                "GET /praticiens" => "Lister tous les praticiens",
                "GET /praticiens/search?specialite=XXX&ville=YYY" => "Rechercher des praticiens par spécialité et/ou ville",
                "GET /praticiens/{id}" => "Afficher les détails d'un praticien"
            ]
        ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
        
        return $response
            ->withHeader('Content-Type', 'application/json')
            ->withStatus(200);
    }
}