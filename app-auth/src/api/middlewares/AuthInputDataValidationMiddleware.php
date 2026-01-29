<?php

namespace toubilib\api\middlewares;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Slim\Psr7\Response as SlimResponse;
use toubilib\core\application\dto\InputAuthDTO;

class AuthInputDataValidationMiddleware
{
    public function __invoke(Request $request, RequestHandler $handler): Response
    {
        $data = (array)$request->getParsedBody();

        $errors = [];

        //cherche les champs requis
        $required = ['email', 'mdp'];
        foreach ($required as $k) {
            if (!isset($data[$k]) || $data[$k] === '') {
                $errors[] = "Le champ '$k' est requis";
            }
        }

        //Capture les erreurs
        if (!empty($errors)) {
            $res = new SlimResponse();
            $res->getBody()->write(json_encode([
                'status' => 'error',
                'errors' => $errors
            ], JSON_UNESCAPED_UNICODE));
            return $res->withHeader('Content-Type', 'application/json')->withStatus(400);
        }

        //Validation du format email
        if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $errors[] = "L'email doit être au format valide (ex: user@example.com)";
        }

        //Capture les erreurs après validation email
        if (!empty($errors)) {
            $res = new SlimResponse();
            $res->getBody()->write(json_encode([
                'status' => 'error',
                'errors' => $errors
            ], JSON_UNESCAPED_UNICODE));
            return $res->withHeader('Content-Type', 'application/json')->withStatus(400);
        }

        $dto = new InputAuthDTO(
            (string)$data['email'],
            (string)$data['mdp']
        );

        $request = $request->withAttribute('inputAuthDto', $dto);

        return $handler->handle($request);
    }
}
