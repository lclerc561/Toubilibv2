<?php
namespace toubilib\api\actions;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use toubilib\api\services\JWTService;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Slim\Psr7\Response as SlimResponse;

/**
 * Action pour rafraîchir un token JWT
 * Permet de renouveler un token expiré (ou proche de l'expiration)
 */
class RefreshTokenAction
{
    private JWTService $jwtService;
    private string $secretKey;

    public function __construct(JWTService $jwtService)
    {
        $this->jwtService = $jwtService;
        $this->secretKey = $_ENV['JWT_SECRET'] ?? throw new \Exception('JWT_SECRET non défini dans .env');
    }

    public function __invoke(Request $request, Response $response): Response
    {
        // Extraire le token du header Authorization
        $authHeader = $request->getHeaderLine('Authorization');

        if (empty($authHeader)) {
            return $this->createErrorResponse('Token manquant', 401);
        }

        $token = str_replace('Bearer ', '', $authHeader);

        try {
            // Décoder le token SANS vérifier l'expiration (on veut pouvoir refresh un token expiré)
            // On vérifie quand même la signature pour éviter les tokens forgés
            JWT::$leeway = 60 * 60 * 24 * 7; // 7 jours de tolérance pour l'expiration
            $decoded = JWT::decode($token, new Key($this->secretKey, 'HS256'));
            JWT::$leeway = 0; // Reset leeway

            $decodedArray = (array) $decoded;
            $userData = (array) $decodedArray['data'];

            // Générer un nouveau token avec les mêmes données utilisateur
            $newToken = $this->jwtService->generateToken([
                'id' => $userData['id'],
                'email' => $userData['email'],
                'role' => (int) $userData['role']
            ]);

            // Déterminer le nom du rôle
            $role = (int) $userData['role'];
            $nomRole = $role === 1 ? 'Patient' : ($role === 10 ? 'Praticien' : 'Inconnu');

            $responseData = [
                'status' => 'success',
                'data' => [
                    'token' => $newToken,
                    'user' => [
                        'id' => $userData['id'],
                        'email' => $userData['email'],
                        'role' => $role . ' - ' . $nomRole
                    ],
                    'expires_in' => 3600
                ]
            ];

            $res = new SlimResponse();
            $res->getBody()->write(json_encode($responseData, JSON_UNESCAPED_UNICODE));
            return $res->withHeader('Content-Type', 'application/json')->withStatus(200);

        } catch (\Firebase\JWT\SignatureInvalidException $e) {
            return $this->createErrorResponse('Token invalide: signature incorrecte', 401);
        } catch (\Firebase\JWT\BeforeValidException $e) {
            return $this->createErrorResponse('Token pas encore valide', 401);
        } catch (\Exception $e) {
            return $this->createErrorResponse('Impossible de rafraîchir le token: ' . $e->getMessage(), 401);
        }
    }

    private function createErrorResponse(string $message, int $status): Response
    {
        $res = new SlimResponse();
        $res->getBody()->write(json_encode([
            'status' => 'error',
            'message' => $message
        ], JSON_UNESCAPED_UNICODE));
        return $res->withHeader('Content-Type', 'application/json')->withStatus($status);
    }
}
