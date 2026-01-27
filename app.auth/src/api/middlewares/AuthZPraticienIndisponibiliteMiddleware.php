<?php
namespace toubilib\api\middlewares;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;

class AuthZPraticienIndisponibiliteMiddleware implements MiddlewareInterface
{
    public function process(Request $request, RequestHandler $handler): Response
    {
        $user = $request->getAttribute('user');
        
        // Extraire l'ID du praticien depuis l'URI
        $path = $request->getUri()->getPath();
        $pathParts = explode('/', trim($path, '/'));
        $praticienId = null;
        
        // Chercher l'ID après '/praticiens/'
        for ($i = 0; $i < count($pathParts) - 1; $i++) {
            if ($pathParts[$i] === 'praticiens' && isset($pathParts[$i + 1])) {
                $praticienId = $pathParts[$i + 1];
                break;
            }
        }

        if (!$user || $user['role'] !== 10) {
            $response = new \Slim\Psr7\Response();
            $response->getBody()->write(json_encode([
                'status' => 'error',
                'message' => 'Accès réservé aux praticiens'
            ], JSON_UNESCAPED_UNICODE));
            return $response->withStatus(403)->withHeader('Content-Type', 'application/json');
        }

        if (!$praticienId) {
            $response = new \Slim\Psr7\Response();
            $response->getBody()->write(json_encode([
                'status' => 'error',
                'message' => 'ID praticien manquant'
            ], JSON_UNESCAPED_UNICODE));
            return $response->withStatus(400)->withHeader('Content-Type', 'application/json');
        }

        // Vérifier que le praticien authentifié est le praticien concerné
        if ($user['id'] !== $praticienId) {
            $response = new \Slim\Psr7\Response();
            $response->getBody()->write(json_encode([
                'status' => 'error',
                'message' => 'Accès non autorisé : vous ne pouvez gérer que vos propres indisponibilités'
            ], JSON_UNESCAPED_UNICODE));
            return $response->withStatus(403)->withHeader('Content-Type', 'application/json');
        }

        return $handler->handle($request);
    }
}

