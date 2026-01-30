<?php
namespace toubilib\core\application\ports;

/**
 * Interface pour les providers d'authentification
 *
 * Définit le contrat que tout provider d'authentification doit respecter.
 */
interface AuthProviderInterface
{
    /**
     * Valide un token et extrait les données utilisateur
     *
     * @param string $token Le token à valider
     * @return array Les données utilisateur
     * @throws \Exception Si le token est invalide ou expiré
     */
    public function validateAndExtractUserData(string $token): array;

    /**
     * Vérifie si un token est valide
     *
     * @param string $token Le token à vérifier
     * @return bool True si valide, false sinon
     */
    public function isTokenValid(string $token): bool;
}
