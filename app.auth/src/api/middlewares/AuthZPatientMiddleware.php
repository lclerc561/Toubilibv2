<?php
namespace toubilib\api\middlewares;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;

class AuthZPatientMiddleware implements MiddlewareInterface
{
    public function process(Request $request, RequestHandler $handler): Response
    {
        $user = $request->getAttribute('user');
        
        if (!$user || $user['role'] !== 1) {
            $response = new \Slim\Psr7\Response();
            $response->getBody()->write(json_encode([
                'status' => 'error',
                'message' => 'Accès réservé aux patients'
            ], JSON_UNESCAPED_UNICODE));
            return $response->withStatus(403)->withHeader('Content-Type', 'application/json');
        }

        // Si la route contient /patients/{id}, vérifier que le patient authentifié = patient demandé
        $path = $request->getUri()->getPath();
        $pathParts = explode('/', trim($path, '/'));
        
        // Chercher l'ID après '/patients/'
        for ($i = 0; $i < count($pathParts) - 1; $i++) {
            if ($pathParts[$i] === 'patients' && isset($pathParts[$i + 1])) {
                $patientId = $pathParts[$i + 1];
                
                // Vérifier que le patient authentifié correspond au patient demandé
                if ($user['id'] !== $patientId) {
                    $response = new \Slim\Psr7\Response();
                    $response->getBody()->write(json_encode([
                        'status' => 'error',
                        'message' => 'Accès non autorisé : vous ne pouvez accéder qu\'à vos propres données'
                    ], JSON_UNESCAPED_UNICODE));
                    return $response->withStatus(403)->withHeader('Content-Type', 'application/json');
                }
                break;
            }
        }

        return $handler->handle($request);
    }
}
