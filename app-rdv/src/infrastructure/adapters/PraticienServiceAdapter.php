<?php

namespace toubilib\infra\adapters;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use toubilib\core\application\ports\PraticienInfoPort;

/**
 * Adaptateur pour communiquer avec le microservice praticiens via HTTP
 *
 * Implémente le port PraticienInfoPort en faisant des appels HTTP
 * vers le microservice app.praticiens, conformément à l'architecture hexagonale.
 */
class PraticienServiceAdapter implements PraticienInfoPort
{
    private Client $client;

    public function __construct(Client $client)
    {
        $this->client = $client;
    }

    /**
     * Vérifie si un praticien existe via l'API
     * 
     * @param string $praticienId
     * @return bool
     */
    public function existePraticien(string $praticienId): bool
    {
        try {
            $response = $this->client->get("/praticiens/{$praticienId}");
            return $response->getStatusCode() === 200;
        } catch (RequestException $e) {
            if ($e->hasResponse() && $e->getResponse()->getStatusCode() === 404) {
                return false;
            }
            // En cas d'erreur de communication, on lève une exception
            throw new \Exception("Erreur de communication avec le service praticiens: " . $e->getMessage());
        }
    }

    /**
     * Récupère les informations d'un praticien via l'API
     * 
     * @param string $praticienId
     * @return array|null Les données du praticien ou null si inexistant
     */
    public function getPraticien(string $praticienId): ?array
    {
        try {
            $response = $this->client->get("/praticiens/{$praticienId}");
            $data = json_decode($response->getBody()->getContents(), true);
            
            if (isset($data['status']) && $data['status'] === 'success' && isset($data['data'])) {
                return $data['data'];
            }
            
            return null;
        } catch (RequestException $e) {
            if ($e->hasResponse() && $e->getResponse()->getStatusCode() === 404) {
                return null;
            }
            throw new \Exception("Erreur de communication avec le service praticiens: " . $e->getMessage());
        }
    }

    /**
     * Récupère les motifs de visite d'un praticien via l'API
     * 
     * @param string $praticienId
     * @return array
     */
    public function getMotifsVisite(string $praticienId): array
    {
        $praticien = $this->getPraticien($praticienId);
        
        if ($praticien === null) {
            return [];
        }
        
        return $praticien['motifsVisite'] ?? [];
    }
}