<?php

namespace toubilib\api\handlers;

use toubilib\core\domain\exceptions\PraticienOccupeException;
use toubilib\core\domain\exceptions\PraticienIndisponibleException;
use toubilib\core\domain\exceptions\RessourceInexistanteException;
use toubilib\core\domain\exceptions\MotifVisiteInvalideException;
use toubilib\core\domain\exceptions\CreneauInvalideException;
use Exception;

/**
 * Gestionnaire centralisé d'exceptions pour l'API
 *
 * Mappe les exceptions métier aux codes HTTP et messages sûrs
 * SÉCURITÉ: Ne jamais exposer les détails techniques ou stack traces
 */
class ExceptionHandler
{
    /**
     * Convertit une exception en code HTTP et message appropriés
     *
     * @param Exception $e L'exception à traiter
     * @return array ['status' => int, 'message' => string]
     */
    public static function handle(Exception $e): array
    {
        // Exceptions métier avec codes HTTP spécifiques
        if ($e instanceof RessourceInexistanteException) {
            return [
                'status' => 404,
                'message' => $e->getMessage()
            ];
        }

        if ($e instanceof PraticienOccupeException || $e instanceof PraticienIndisponibleException) {
            return [
                'status' => 409,
                'message' => $e->getMessage()
            ];
        }

        if ($e instanceof MotifVisiteInvalideException || $e instanceof CreneauInvalideException) {
            return [
                'status' => 400,
                'message' => $e->getMessage()
            ];
        }

        // Exception générique - ne pas exposer le message d'origine
        // qui pourrait contenir des détails techniques
        return [
            'status' => 500,
            'message' => 'Une erreur interne est survenue'
        ];
    }

    /**
     * Détermine si une exception doit être loggée
     *
     * Les exceptions 4xx sont des erreurs utilisateur (pas besoin de log détaillé)
     * Les exceptions 5xx sont des erreurs serveur (nécessitent un log)
     */
    public static function shouldLog(Exception $e): bool
    {
        $info = self::handle($e);
        return $info['status'] >= 500;
    }
}
