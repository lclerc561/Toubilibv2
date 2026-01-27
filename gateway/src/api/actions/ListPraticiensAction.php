<?php

namespace toubilib\gateway\api\actions;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\ClientException;
use Slim\Exception\HttpNotFoundException;

class ListPraticiensAction
{
    private ClientInterface $remote_practicien_service;

    public function __construct(ClientInterface $client)
    {
        $this->remote_practicien_service = $client;
    }

    public function __invoke(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        try {
            $response = $this->remote_practicien_service->get("praticiens");
        } catch (ClientException $e) {
            throw new HttpNotFoundException($request, "Praticiens non trouvés");
        }
        return $response;
    }
}
