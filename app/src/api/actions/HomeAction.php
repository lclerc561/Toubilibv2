<?php

namespace toubilib\api\actions;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

class HomeAction
{
    public function __invoke(Request $request, Response $response): Response
    {
        $response->getBody()->write(json_encode([
            'message' => "Bienvenue sur l'API Toubilib",
            'endpoints' => [
                // Authentification
                "POST /auth/login" => "Authentification de l'utilisateur (patient ou praticien)",
                "POST /auth/register" => "S'inscrire en tant que patient (fonctionnalité 12)",
                
                // Praticiens
                "GET /praticiens" => "Lister tous les praticiens",
                "GET /praticiens/search?specialite=XXX&ville=YYY" => "Rechercher des praticiens par spécialité et/ou ville (fonctionnalité 9)",
                "GET /praticiens/{id}" => "Afficher les détails d'un praticien",
                "GET /praticiens/{id}/rdvs/occupes?dateDebut=YYYY-MM-DD HH:MM:SS&dateFin=YYYY-MM-DD HH:MM:SS" => "Lister les créneaux occupés d'un praticien",
                "GET /praticiens/{id}/agenda?dateDebut=YYYY-MM-DD HH:MM:SS&dateFin=YYYY-MM-DD HH:MM:SS" => "Consulter l'agenda d'un praticien (praticien authentifié uniquement)",
                
                // Indisponibilités (fonctionnalité 13)
                "POST /praticiens/{id}/indisponibilites" => "Créer une indisponibilité temporaire (praticien authentifié uniquement)",
                "GET /praticiens/{id}/indisponibilites" => "Lister les indisponibilités d'un praticien (praticien authentifié uniquement)",
                "DELETE /praticiens/{id}/indisponibilites/{indisponibiliteId}" => "Supprimer une indisponibilité (praticien authentifié uniquement)",
                
                // Rendez-vous
                "POST /rdvs" => "Créer un rendez-vous (patient authentifié uniquement)",
                "GET /rdvs/{id}" => "Consulter un rendez-vous (patient ou praticien du RDV)",
                "DELETE /rdvs/{id}/annuler" => "Annuler un rendez-vous (patient ou praticien du RDV)",
                "PATCH /rdvs/{id}/honorer" => "Marquer un RDV comme honoré (praticien propriétaire uniquement, fonctionnalité 10)",
                "PATCH /rdvs/{id}/non-honorer" => "Marquer un RDV comme non honoré (praticien propriétaire uniquement, fonctionnalité 10)",
                
                // Patients
                "GET /patients/{id}" => "Afficher les détails d'un patient (patient authentifié uniquement)",
                "GET /patients/{id}/consultations" => "Obtenir l'historique des consultations d'un patient (patient authentifié uniquement, fonctionnalité 11)"
            ],
            'notes' => [
                "Comptes de test:",
                "  - Patient: Denis.Teixeira@hotmail.fr / test",
                "  - Praticien: dith.Didier@club-internet.fr / test",
                "Tous les endpoints nécessitant une authentification requièrent un header: Authorization: Bearer {token}"
            ]
        ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
        return $response
            ->withHeader('Content-Type', 'application/json')
            ->withStatus(200);
    }
}