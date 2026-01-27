<?php

namespace toubilib\api\actions;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use toubilib\core\application\usecases\ServiceAuthInterface;
use toubilib\api\services\JWTService;
use toubilib\api\services\HATEOASService;
use Slim\Psr7\Response as SlimResponse;

class AuthLoginAction
{
    private ServiceAuthInterface $serviceAuth;
    private JWTService $jwtService;
    private HATEOASService $hateoasService;

    public function __construct(ServiceAuthInterface $serviceAuth, JWTService $jwtService, HATEOASService $hateoasService)
    {
        $this->serviceAuth = $serviceAuth;
        $this->jwtService = $jwtService;
        $this->hateoasService = $hateoasService;
    }

    public function __invoke(Request $request, Response $response, array $args): Response
    {
        $dto = $request->getAttribute('inputAuthDto');
        if (! $dto) {
            return $this->createErrorResponse('Erreur de configuration serveur', 500);
        }

        try {
            $auth = $this->serviceAuth->authentifier($dto->email, $dto->mdp);

            if (!$auth) {
                return $this->createErrorResponse('Email ou mot de passe incorrect', 401);
            }


            // Déterminer le nom du rôle
            if ($auth->role === 1) {
                $nomRole = 'Patient';
            } elseif ($auth->role === 10) {
                $nomRole = 'Praticien';
            } else {
                $nomRole = 'Inconnu';
            }

            // Générer le token JWT
            $tokenPayload = [
                'id' => $auth->id,
                'email' => $auth->email,
                'role' => $auth->role
            ];
            
            $token = $this->jwtService->generateToken($tokenPayload);

            $responseData = [
                'status' => 'success',
                'data' => [
                    'token' => $token,
                    'user' => [
                        'id' => $auth->id,
                        'email' => $auth->email,
                        'role' => $auth->role . ' - ' . $nomRole
                    ],
                    'expires_in' => 3600 // 1 heure en secondes
                ],
                '_links' => $this->hateoasService->getAuthLinks()
            ];

            $res = new SlimResponse();
            $res->getBody()->write(json_encode($responseData, JSON_UNESCAPED_UNICODE));
            return $res->withHeader('Content-Type', 'application/json')->withStatus(200);
        } catch (\Exception $e) {
            $status = 500; // Erreur serveur par défaut
            $msg = "Une erreur est survenue lors de l'authentification";
            
            // Seulement pour les erreurs de validation métier spécifiques
            if (strpos($e->getMessage(), 'inexistant') !== false) {
                $status = 404;
                $msg = $e->getMessage();
            } elseif (strpos($e->getMessage(), 'invalide') !== false) {
                $status = 400;
                $msg = $e->getMessage();
            }

            return $this->createErrorResponse($msg, $status);
        }
    }

    private function createErrorResponse(string $message, int $status = 500): Response
    {
        $res = new SlimResponse();
        $res->getBody()->write(json_encode([
            'status' => 'error',
            'message' => $message
        ], JSON_UNESCAPED_UNICODE));
        return $res->withHeader('Content-Type', 'application/json')->withStatus($status);
    }
}
