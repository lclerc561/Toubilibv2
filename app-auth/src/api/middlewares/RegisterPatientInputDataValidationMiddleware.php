<?php

namespace toubilib\api\middlewares;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Slim\Psr7\Response as SlimResponse;
use toubilib\core\application\dto\InputRegisterPatientDTO;
use toubilib\core\application\ports\AuthRepositoryInterface;

class RegisterPatientInputDataValidationMiddleware
{
    private AuthRepositoryInterface $authRepository;

    public function __construct(AuthRepositoryInterface $authRepository)
    {
        $this->authRepository = $authRepository;
    }

    public function __invoke(Request $request, RequestHandler $handler): Response
    {
        $data = (array)$request->getParsedBody();

        $errors = [];

        // Champs requis
        $required = ['email', 'mdp', 'nom', 'prenom', 'telephone'];
        foreach ($required as $field) {
            if (!isset($data[$field]) || $data[$field] === '') {
                $errors[] = "Le champ '$field' est requis";
            }
        }

        // Validation du format email
        if (isset($data['email']) && $data['email'] !== '') {
            if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
                $errors[] = "L'email doit être au format valide (ex: user@example.com)";
            } else {
                // Vérifier que l'email n'existe pas déjà
                $existingAuth = $this->authRepository->findByEmail($data['email']);
                if ($existingAuth !== null) {
                    $errors[] = "Cet email est déjà utilisé";
                }
            }
        }

        // Validation du mot de passe (minimum 6 caractères)
        if (isset($data['mdp']) && strlen($data['mdp']) < 6) {
            $errors[] = "Le mot de passe doit contenir au moins 6 caractères";
        }

        // Validation de la date de naissance si fournie
        if (isset($data['date_naissance']) && $data['date_naissance'] !== '') {
            $date = \DateTime::createFromFormat('Y-m-d', $data['date_naissance']);
            if (!$date || $date->format('Y-m-d') !== $data['date_naissance']) {
                $errors[] = "La date de naissance doit être au format YYYY-MM-DD";
            }
        }

        // Si erreurs, retourner 400
        if (!empty($errors)) {
            $res = new SlimResponse();
            $res->getBody()->write(json_encode([
                'status' => 'error',
                'errors' => $errors
            ], JSON_UNESCAPED_UNICODE));
            return $res->withHeader('Content-Type', 'application/json')->withStatus(400);
        }

        // Créer le DTO
        $dto = new InputRegisterPatientDTO(
            (string)$data['email'],
            (string)$data['mdp'],
            (string)$data['nom'],
            (string)$data['prenom'],
            (string)$data['telephone'],
            isset($data['date_naissance']) && $data['date_naissance'] !== '' ? (string)$data['date_naissance'] : null,
            isset($data['adresse']) && $data['adresse'] !== '' ? (string)$data['adresse'] : null,
            isset($data['code_postal']) && $data['code_postal'] !== '' ? (string)$data['code_postal'] : null,
            isset($data['ville']) && $data['ville'] !== '' ? (string)$data['ville'] : null
        );

        $request = $request->withAttribute('inputRegisterPatientDto', $dto);

        return $handler->handle($request);
    }
}
