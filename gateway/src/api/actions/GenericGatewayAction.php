<?php

namespace toubilib\gateway\api\actions;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Slim\Exception\HttpNotFoundException;
use Slim\Exception\HttpInternalServerErrorException;

class GenericGatewayAction {
    private Client $clientToubilib;
    private Client $clientPraticiens;
    private Client $clientRdv;
    private Client $clientAuth;

    public function __construct(Client $clientToubilib, Client $clientPraticiens, Client $clientRdv, Client $clientAuth) {
        $this->clientToubilib = $clientToubilib;
        $this->clientPraticiens = $clientPraticiens;
        $this->clientRdv = $clientRdv;
        $this->clientAuth = $clientAuth;
    }

    public function __invoke(Request $request, Response $response, array $args): Response {
    $method = $request->getMethod();
    
    $path = $args['routes'] ?? ltrim($request->getUri()->getPath(), '/');
    
    $client = $this->selectClient($path);

    $targetPath = $path;
    if (str_starts_with($path, 'auth/')) {
        $targetPath = str_replace('auth/', '', $path);
    }

    $headers = $request->getHeaders();
    unset($headers['Host']);

    try {
        $apiResponse = $client->request($method, $targetPath, [
            'query' => $request->getQueryParams(),
            'headers' => $headers,
            'body' => $request->getBody(),
            'http_errors' => true
        ]);
        
        $response->getBody()->write($apiResponse->getBody()->getContents());
        return $response->withStatus($apiResponse->getStatusCode())
                        ->withHeader('Content-Type', 'application/json');
    } catch (RequestException $e) {
    // Si le microservice a renvoyé une réponse (401, 403, 400, etc.)
    if ($e->hasResponse()) {
        $apiResponse = $e->getResponse();
        $statusCode = $apiResponse->getStatusCode();
        $response->getBody()->write($apiResponse->getBody()->getContents());
        return $response->withStatus($statusCode)
                        ->withHeader('Content-Type', 'application/json');
    }
    // Si c'est un vrai problème technique
    throw new HttpInternalServerErrorException($request, "Microservice injoignable", $e);
}
}

    /**
     * Sélectionne le bon client selon le path de la requête
     *
     * @param string $path Le chemin de la requête
     * @return Client Le client Guzzle approprié
     */
    private function selectClient(string $path): Client {
        $service = null;

        // --- Routes d'authentification (Microservice app.auth) ---
        if (str_starts_with($path, 'auth') || str_starts_with($path, 'tokens')) {
            $service = 'app.auth';
            $client = $this->clientAuth;
        }
        // Routes spécifiques praticiens (avec RDV) → microservice rdv
        // IMPORTANT : Vérifier ces routes AVANT la route générique praticiens !
        elseif (preg_match('#^praticiens/[^/]+/(agenda|rdvs/occupes)#', $path)) {
            $service = 'app.rdv';
            $client = $this->clientRdv;
        }
        // Routes praticiens génériques → microservice praticiens
        elseif (str_starts_with($path, 'praticiens')) {
            $service = 'app.praticiens';
            $client = $this->clientPraticiens;
        }
        // Routes RDV, patients → microservice rdv
        elseif (str_starts_with($path, 'rdvs') || str_starts_with($path, 'patients')) {
            $service = 'app.rdv';
            $client = $this->clientRdv;
        }
        // Reste vers le monolithe api.toubilib
        else {
            $service = 'api.toubilib';
            $client = $this->clientToubilib;
        }

        // Logging pour débogage et monitoring
        error_log(sprintf("[Gateway] Routing %s → %s", $path, $service));

        return $client;
    }
}
