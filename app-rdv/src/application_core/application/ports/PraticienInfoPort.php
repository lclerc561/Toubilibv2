<?php

namespace toubilib\core\application\ports;

/**
 * Port pour récupérer des informations sur les praticiens
 *
 * Cette interface définit le contrat pour accéder aux informations des praticiens,
 * conformément à l'architecture hexagonale.
 *
 * Dans le contexte microservices, cette interface sera implémentée par un adaptateur
 * qui communique avec le microservice praticiens via HTTP.
 */
interface PraticienInfoPort
{
    /**
     * Vérifie si un praticien existe
     *
     * @param string $praticienId L'identifiant du praticien
     * @return bool true si le praticien existe, false sinon
     * @throws \Exception En cas d'erreur de communication
     */
    public function existePraticien(string $praticienId): bool;

    /**
     * Récupère les informations complètes d'un praticien
     *
     * @param string $praticienId L'identifiant du praticien
     * @return array|null Les données du praticien ou null si inexistant
     * @throws \Exception En cas d'erreur de communication
     */
    public function getPraticien(string $praticienId): ?array;

    /**
     * Récupère les motifs de visite autorisés pour un praticien
     *
     * @param string $praticienId L'identifiant du praticien
     * @return array La liste des motifs de visite
     * @throws \Exception En cas d'erreur de communication
     */
    public function getMotifsVisite(string $praticienId): array;
}
