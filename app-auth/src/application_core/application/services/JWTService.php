<?php

namespace toubilib\core\application\services;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Firebase\JWT\ExpiredException;
use Firebase\JWT\SignatureInvalidException;
use Exception;

class JWTService
{
    private string $secretKey;
    private string $algorithm;
    private int $expirationTime;

    public function __construct()
    {
        $this->secretKey = $_ENV['JWT_SECRET'] ?? throw new \Exception('JWT_SECRET non défini dans .env');
        $this->algorithm = 'HS256';
        $this->expirationTime = 3600; // 1 heure
    }

    /**
     * Génère un token JWT pour un utilisateur
     */
    public function generateToken(array $payload): string
    {
        $now = time();
        
        $tokenPayload = [
            'iss' => 'toubilib-api', // Issuer
            'aud' => 'toubilib-client', // Audience
            'iat' => $now, // Issued at
            'exp' => $now + $this->expirationTime, // Expiration
            'sub' => $payload['id'], // Subject (user ID)
            'data' => [
                'id' => $payload['id'],
                'email' => $payload['email'],
                'role' => $payload['role']
            ]
        ];

        return JWT::encode($tokenPayload, $this->secretKey, $this->algorithm);
    }

    /**
     * Valide et décode un token JWT
     */
    public function validateToken(string $token): array
    {
        try {
            $decoded = JWT::decode($token, new Key($this->secretKey, $this->algorithm));
            return (array) $decoded;
        } catch (ExpiredException $e) {
            throw new Exception('Token expiré');
        } catch (SignatureInvalidException $e) {
            throw new Exception('Token invalide');
        } catch (Exception $e) {
            throw new Exception('Erreur de validation du token: ' . $e->getMessage());
        }
    }

    /**
     * Extrait les données utilisateur du token
     */
    public function getUserDataFromToken(string $token): array
    {
        $decoded = $this->validateToken($token);
        $data = (array) $decoded['data'];
        
        // S'assurer que le rôle est un entier
        if (isset($data['role'])) {
            $data['role'] = (int) $data['role'];
        }
        
        return $data;
    }

    /**
     * Vérifie si un token est valide (sans lever d'exception)
     */
    public function isTokenValid(string $token): bool
    {
        try {
            $this->validateToken($token);
            return true;
        } catch (Exception $e) {
            return false;
        }
    }
}
