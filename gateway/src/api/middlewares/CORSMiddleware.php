<?php
namespace toubilib\gateway\api\middlewares;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;

/**
 * Middleware CORS avec restriction des origines
 *
 * SÉCURITÉ: En production, définir ALLOWED_ORIGINS avec les domaines autorisés
 * Exemple: ALLOWED_ORIGINS=https://www.example.com,https://app.example.com
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
            // Configuration par défaut pour le développement
            // ATTENTION: À restreindre en production!
            $this->allowedOrigins = [
                'http://localhost:3000',
                'http://localhost:4200',
                'http://localhost:8080',
                'http://localhost:5173',
            ];
        }
    }

    public function process(Request $request, RequestHandler $handler): Response
    {
        $origin = $request->getHeaderLine('Origin');

        // Vérifier si l'origine est autorisée
        $allowedOrigin = $this->isOriginAllowed($origin) ? $origin : $this->allowedOrigins[0] ?? '';

        // Gérer les requêtes OPTIONS (preflight)
        if ($request->getMethod() === 'OPTIONS') {
            $response = new \Slim\Psr7\Response();
            $response = $this->addCorsHeaders($response, $allowedOrigin);
            return $response->withStatus(200);
        }

        // Pour les autres requêtes, appeler le handler puis ajouter les headers CORS
        $response = $handler->handle($request);
        $response = $this->addCorsHeaders($response, $allowedOrigin);

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
        $response = $response->withHeader('Access-Control-Max-Age', '86400'); // 24 heures

        return $response;
    }
}
