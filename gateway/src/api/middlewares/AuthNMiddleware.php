<?php

namespace toubilib\gateway\api\middlewares;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use GuzzleHttp\Client;
use Slim\Psr7\Response as SlimResponse;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\ConnectException;

class AuthNMiddleware implements MiddlewareInterface
{
    private Client $authClient;

    public function __construct(Client $authClient)
    {
        $this->authClient = $authClient;
    }

    public function process(Request $request, RequestHandler $handler): Response
    {
        $authHeader = $request->getHeaderLine('Authorization');
        if (!$authHeader) {
            return $this->unauthorized("Token d'authentification manquant");
        }

        try {
            //Requête de validation au microservice d'authentification
            $response = $this->authClient->get('/tokens/validate', [
                'headers' => ['Authorization' => $authHeader]
            ]);

            if ($response->getStatusCode() === 200) {
                //prochain middleware ou l'action
                return $handler->handle($request);
            }
        } catch (ClientException $e) {
            // C'est une erreur 4xx (le service d'auth a répondu que le token est mauvais)
            return $this->unauthorized("Jeton JWT invalide ou expiré");
        } catch (ConnectException $e) {
            // Problème de réseau (le microservice app.auth est peut-être éteint)
            return $this->errorResponse("Service d'authentification injoignable", 503);
        } catch (\Exception $e) {
            // Toute autre erreur (ex: mauvaise route /tokens/validate)
            return $this->errorResponse("Erreur interne du middleware : " . $e->getMessage(), 500);
        }

        return $this->unauthorized("Authentification échouée");
    }

    private function errorResponse(string $message, int $status): Response
    {
        $response = new \Slim\Psr7\Response();
        $response->getBody()->write(json_encode([
            'type' => 'error',
            'error' => $status,
            'message' => $message
        ]));
        return $response->withStatus($status)->withHeader('Content-Type', 'application/json');
    }

    private function unauthorized(string $message): Response
    {
        $response = new SlimResponse();
        $response->getBody()->write(json_encode([
            'type' => 'error',
            'error' => 401,
            'message' => $message
        ]));
        return $response->withStatus(401)->withHeader('Content-Type', 'application/json');
    }
}
