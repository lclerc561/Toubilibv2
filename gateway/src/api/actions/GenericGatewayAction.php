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

    public function __construct(Client $clientToubilib, Client $clientPraticiens, Client $clientRdv) {
        $this->clientToubilib = $clientToubilib;
        $this->clientPraticiens = $clientPraticiens;
        $this->clientRdv = $clientRdv;
    }

    public function __invoke(Request $request, Response $response, array $args): Response {
        $method = $request->getMethod();
        $path = $args['routes'] ?? '';

        // Déterminer quel microservice utiliser en fonction du path
        $client = $this->selectClient($path);

        $headers = $request->getHeaders();
        unset($headers['Host']);
        unset($headers['Content-Length']);

        try {
            $apiResponse = $client->request($method, $path, [
                'query' => $request->getQueryParams(),
                'headers' => $headers,
                'body' => $request->getBody(),
                'http_errors' => true
            ]);
        } catch (RequestException $e) {
            if ($e->hasResponse()) {
                $statusCode = $e->getResponse()->getStatusCode();
                if ($statusCode === 404) {
                    throw new HttpNotFoundException($request, "Ressource introuvable sur le service distant : $path");
                }
            }
            throw new HttpInternalServerErrorException($request, "Erreur Gateway vers : $path", $e);
        }
        
        $response->getBody()->write($apiResponse->getBody()->getContents());
        return $response->withStatus($apiResponse->getStatusCode())
            ->withHeader('Content-Type', 'application/json');
    }

    /**
     * Sélectionne le bon client selon le path de la requête
     * 
     * @param string $path Le chemin de la requête
     * @return Client Le client Guzzle approprié
     */
    private function selectClient(string $path): Client {
        // Routes spécifiques praticiens (avec RDV) → microservice rdv
        // IMPORTANT : Vérifier ces routes AVANT la route générique praticiens !
        if (preg_match('#^praticiens/[^/]+/(agenda|rdvs/occupes)#', $path)) {
            return $this->clientRdv;
        }
        
        // Routes praticiens génériques (liste, détails, recherche) → microservice praticiens
        if (str_starts_with($path, 'praticiens')) {
            return $this->clientPraticiens;
        }
        
        // Routes RDV, patients → microservice rdv
        if (str_starts_with($path, 'rdvs') || 
            str_starts_with($path, 'patients')) {
            return $this->clientRdv;
        }
        
        // Routes auth, ou tout le reste → API complète toubilib
        return $this->clientToubilib;
    }
}