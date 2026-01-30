<?php
namespace toubilib\core\application\services;

class HATEOASService
{
    private string $baseUrl;

    public function __construct()
    {
        $this->baseUrl = $_ENV['API_BASE_URL'] ?? 'http://localhost:6080';
    }

    /**
     * Génère les liens HATEOAS pour un praticien
     */
    public function getPraticienLinks(string $praticienId): array
    {
        return [
            'self' => [
                'href' => "{$this->baseUrl}/praticiens/{$praticienId}",
                'method' => 'GET'
            ],
            'agenda' => [
                'href' => "{$this->baseUrl}/praticiens/{$praticienId}/agenda",
                'method' => 'GET',
                'description' => 'Consulter l\'agenda du praticien'
            ],
            'creneaux_occupes' => [
                'href' => "{$this->baseUrl}/praticiens/{$praticienId}/rdvs/occupes",
                'method' => 'GET',
                'description' => 'Lister les créneaux occupés'
            ]
        ];
    }

    /**
     * Génère les liens HATEOAS pour un RDV
     */
    public function getRDVLinks(string $rdvId): array
    {
        return [
            'self' => [
                'href' => "{$this->baseUrl}/rdvs/{$rdvId}",
                'method' => 'GET'
            ],
            'cancel' => [
                'href' => "{$this->baseUrl}/rdvs/{$rdvId}",
                'method' => 'DELETE',
                'description' => 'Annuler le rendez-vous'
            ]
        ];
    }

    /**
     * Génère les liens HATEOAS pour un patient
     */
    public function getPatientLinks(string $patientId): array
    {
        return [
            'self' => [
                'href' => "{$this->baseUrl}/patients/{$patientId}",
                'method' => 'GET'
            ],
            'consultations' => [
                'href' => "{$this->baseUrl}/patients/{$patientId}/consultations",
                'method' => 'GET',
                'description' => 'Lister les consultations du patient'
            ]
        ];
    }

    /**
     * Génère les liens HATEOAS pour la liste des praticiens
     */
    public function getPraticiensListLinks(): array
    {
        return [
            'self' => [
                'href' => "{$this->baseUrl}/praticiens",
                'method' => 'GET'
            ],
            'auth' => [
                'href' => "{$this->baseUrl}/auth/login",
                'method' => 'POST',
                'description' => 'S\'authentifier'
            ]
        ];
    }

    /**
     * Génère les liens HATEOAS pour l'authentification
     */
    public function getAuthLinks(): array
    {
        return [
            'self' => [
                'href' => "{$this->baseUrl}/auth/login",
                'method' => 'POST'
            ],
            'praticiens' => [
                'href' => "{$this->baseUrl}/praticiens",
                'method' => 'GET',
                'description' => 'Lister les praticiens'
            ]
        ];
    }

    /**
     * Génère les liens HATEOAS pour l'agenda d'un praticien
     */
    public function getAgendaLinks(string $praticienId): array
    {
        return [
            'self' => [
                'href' => "{$this->baseUrl}/praticiens/{$praticienId}/agenda",
                'method' => 'GET'
            ],
            'praticien' => [
                'href' => "{$this->baseUrl}/praticiens/{$praticienId}",
                'method' => 'GET',
                'description' => 'Détails du praticien'
            ],
            'creneaux_occupes' => [
                'href' => "{$this->baseUrl}/praticiens/{$praticienId}/rdvs/occupes",
                'method' => 'GET',
                'description' => 'Créneaux occupés'
            ]
        ];
    }

    /**
     * Génère les liens HATEOAS pour créer un RDV
     */
    public function getCreateRDVLinks(): array
    {
        return [
            'create' => [
                'href' => "{$this->baseUrl}/rdvs",
                'method' => 'POST',
                'description' => 'Créer un nouveau rendez-vous'
            ],
            'praticiens' => [
                'href' => "{$this->baseUrl}/praticiens",
                'method' => 'GET',
                'description' => 'Lister les praticiens disponibles'
            ]
        ];
    }
}
