<?php

namespace toubilib\api\middlewares;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Slim\Psr7\Response as SlimResponse;
use toubilib\core\application\ports\AuthProviderInterface;

/**
 * Middleware d'authentification
 *
 * Valide la présence et la validité d'un token JWT dans la requête.
 * Les middlewares d'autorisation (AuthZ*) peuvent ensuite utiliser les données du token validé.
 */
class AuthNMiddleware implements MiddlewareInterface
{
    private AuthProviderInterface $authProvider;

    public function __construct(AuthProviderInterface $authProvider)
    {
        $this->authProvider = $authProvider;
    }

    public function process(Request $request, RequestHandler $handler): Response
    {
        $authHeader = $request->getHeaderLine('Authorization');

        if (!$authHeader) {
            return $this->unauthorized("Token d'authentification manquant");
        }

        // Extraire le token (format: "Bearer <token>")
        $token = str_replace('Bearer ', '', $authHeader);

        if (empty($token)) {
            return $this->unauthorized("Token d'authentification manquant");
        }

        try {
            // Valider le token avec vérification de signature
            $userData = $this->authProvider->validateAndExtractUserData($token);

            // Stocker les données utilisateur validées dans les attributs de la requête
            // pour que les middlewares suivants puissent les utiliser
            $request = $request->withAttribute('user', $userData);

            return $handler->handle($request);
        } catch (\Exception $e) {
            return $this->unauthorized($e->getMessage());
        }
    }

    private function unauthorized(string $message): Response
    {
        $response = new SlimResponse();
        $response->getBody()->write(json_encode([
            'status' => 'error',
            'message' => $message
        ], JSON_UNESCAPED_UNICODE));
        return $response->withStatus(401)->withHeader('Content-Type', 'application/json');
    }
}
