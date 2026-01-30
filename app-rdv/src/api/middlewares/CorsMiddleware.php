<?php
namespace toubilib\api\middlewares;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;

/**
 * Middleware CORS avec restriction des origines
 *
 * NOTE: Ce microservice devrait être accessible uniquement via la gateway.
 * Le CORS est géré au niveau de la gateway. Ce middleware est conservé
 * pour les tests directs en développement uniquement.
 *
 * SÉCURITÉ: En production, bloquer l'accès direct à ce microservice
 * et laisser uniquement la gateway y accéder.
 */
class CORSMiddleware implements MiddlewareInterface
{
    private array $allowedOrigins;

    public function __construct()
    {
        // Récupérer les origines autorisées depuis la variable d'environnement
        $originsEnv = $_ENV['ALLOWED_ORIGINS'] ?? '';

        if (!empty($originsEnv)) {
            $this->allowedOrigins = array_map('trim', explode(',', $originsEnv));
        } else {
            // Configuration par défaut pour le développement local
            $this->allowedOrigins = [
                'http://localhost:3000',
                'http://localhost:4200',
                'http://localhost:8080',
                'http://localhost:5173',
                'http://localhost:6081', // Gateway
            ];
        }
    }

    public function process(Request $request, RequestHandler $handler): Response
    {
        $origin = $request->getHeaderLine('Origin');

        // Vérifier si l'origine est autorisée
        $allowedOrigin = $this->isOriginAllowed($origin) ? $origin : '';

        // Gérer les requêtes OPTIONS (preflight)
        if ($request->getMethod() === 'OPTIONS') {
            $response = new \Slim\Psr7\Response();
            if ($allowedOrigin) {
                $response = $this->addCorsHeaders($response, $allowedOrigin);
            }
            return $response->withStatus(200);
        }

        // Pour les autres requêtes
        $response = $handler->handle($request);

        if ($allowedOrigin) {
            $response = $this->addCorsHeaders($response, $allowedOrigin);
        }

        return $response;
    }

    /**
     * Vérifie si une origine est autorisée
     */
    private function isOriginAllowed(string $origin): bool
    {
        if (empty($origin)) {
            return false;
        }

        return in_array($origin, $this->allowedOrigins, true);
    }

    /**
     * Ajoute les headers CORS à la réponse
     */
    private function addCorsHeaders(Response $response, string $allowedOrigin): Response
    {
        $response = $response->withHeader('Access-Control-Allow-Origin', $allowedOrigin);
        $response = $response->withHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, PATCH, OPTIONS');
        $response = $response->withHeader('Access-Control-Allow-Headers', 'Content-Type, Authorization, X-Requested-With');
        $response = $response->withHeader('Access-Control-Allow-Credentials', 'true');
        $response = $response->withHeader('Access-Control-Max-Age', '86400');

        return $response;
    }
}
