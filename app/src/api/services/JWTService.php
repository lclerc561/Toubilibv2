<?php

namespace toubilib\api\services;

use Firebase\JWT\JWT;

/**
 * Service pour la génération de tokens JWT
 * Séparé de l'authentification (validation) pour respecter le SRP
 */
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
     * 
     * @param array $payload Les données utilisateur (id, email, role)
     * @return string Le token JWT généré
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
}