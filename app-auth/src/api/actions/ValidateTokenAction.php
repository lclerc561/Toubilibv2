<?php

namespace toubilib\api\actions;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use toubilib\core\application\ports\AuthProviderInterface;
use Slim\Psr7\Response as SlimResponse;

class ValidateTokenAction
{
    private AuthProviderInterface $authProvider;

    public function __construct(AuthProviderInterface $authProvider)
    {
        $this->authProvider = $authProvider;
    }

    public function __invoke(Request $request, Response $response): Response
    {
        // Extraction du token (Header Authorization: Bearer <token>)
        $authHeader = $request->getHeaderLine('Authorization');
        $token = str_replace('Bearer ', '', $authHeader);

        if (empty($token)) {
            return $this->jsonResponse($response, ['message' => 'Token manquant'], 401);
        }

        try {
            // Vérification auprès du provider JWT
            $userData = $this->authProvider->validateAndExtractUserData($token);

            // Succès : 200 OK
            return $this->jsonResponse($response, [
                'status' => 'success',
                'user' => $userData
            ], 200);

        } catch (\Exception $e) {
            // Échec : 401 avec message d'erreur (invalide, expiré...)
            return $this->jsonResponse($response, [
                'status' => 'error',
                'message' => $e->getMessage()
            ], 401);
        }
    }

    private function jsonResponse(Response $response, array $data, int $status): Response
    {
        $response->getBody()->write(json_encode($data, JSON_UNESCAPED_UNICODE));
        return $response->withHeader('Content-Type', 'application/json')->withStatus($status);
    }
}