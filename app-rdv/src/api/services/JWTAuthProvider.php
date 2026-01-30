<?php

namespace toubilib\api\services;

use toubilib\core\application\ports\AuthProviderInterface;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Firebase\JWT\ExpiredException;
use Firebase\JWT\SignatureInvalidException;
use Exception;

/**
 * Implémentation de l'authentification basée sur JWT
 * Adaptateur qui se place dans la couche API pour valider les tokens
 */
class JWTAuthProvider implements AuthProviderInterface
{
    private string $secretKey;
    private string $algorithm;

    public function __construct(string $secretKey)
    {
        if (empty($secretKey)) {
            throw new Exception('JWT_SECRET ne peut pas être vide');
        }
        $this->secretKey = $secretKey;
        $this->algorithm = 'HS256';
    }

    /**
     * Valide un token JWT et extrait les données utilisateur
     *
     * @param string $token Le token JWT à valider
     * @return array Les données utilisateur ['id' => ..., 'email' => ..., 'role' => ...]
     * @throws Exception Si le token est invalide ou expiré
     */
    public function validateAndExtractUserData(string $token): array
    {
        try {
            $decoded = JWT::decode($token, new Key($this->secretKey, $this->algorithm));
            $decodedArray = (array) $decoded;
            $data = (array) $decodedArray['data'];

            // S'assurer que le rôle est un entier
            if (isset($data['role'])) {
                $data['role'] = (int) $data['role'];
            }

            return $data;
        } catch (ExpiredException $e) {
            throw new Exception('Token expiré');
        } catch (SignatureInvalidException $e) {
            throw new Exception('Signature du token invalide');
        } catch (Exception $e) {
            throw new Exception('Erreur de validation du token: ' . $e->getMessage());
        }
    }

    /**
     * Vérifie si un token est valide sans lever d'exception
     *
     * @param string $token Le token à vérifier
     * @return bool True si valide, false sinon
     */
    public function isTokenValid(string $token): bool
    {
        try {
            $this->validateAndExtractUserData($token);
            return true;
        } catch (Exception $e) {
            return false;
        }
    }
}
