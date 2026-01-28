<?php

namespace toubilib\gateway\api\middlewares;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use GuzzleHttp\Client;
use Slim\Psr7\Response as SlimResponse;

class AuthNMiddleware implements MiddlewareInterface
{
    private Client $authClient;

    public function __construct(Client $authClient)
    {
        // Client configuré pour pointer vers http://api.auth
        $this->authClient = $authClient;
    }

    public function process(Request $request, RequestHandler $handler): Response
    {
        //Extraire le token
        $authHeader = $request->getHeaderLine('Authorization');
        if (!$authHeader || !preg_match('/Bearer\s(\S+)/', $authHeader, $matches)) {
            return $this->errorResponse("Token d'authentification manquant", 401);
        }

        $token = $matches[1];

        try {
            //Adresser une requête de validation au microservice d'authentification
            $response = $this->authClient->get('/tokens/validate', [
                'headers' => ['Authorization' => "Bearer $token"]
            ]);

            if ($response->getStatusCode() === 200) {
                // Requête transmise au middleware suivant
                $userData = json_decode($response->getBody()->getContents(), true);
                $request = $request->withAttribute('user', $userData['user']);
                
                return $handler->handle($request);
            }
        } catch (\Exception $e) {
            return $this->errorResponse("Token invalide ou expiré : " . $e->getMessage(), 401);
        }

        return $this->errorResponse("Authentification échouée", 401);
    }

    private function errorResponse(string $message, int $status): Response
    {
        $response = new SlimResponse();
        $response->getBody()->write(json_encode(['status' => 'error', 'message' => $message]));
        return $response->withHeader('Content-Type', 'application/json')->withStatus($status);
    }
}