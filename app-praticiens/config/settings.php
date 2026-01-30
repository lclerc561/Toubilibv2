<?php

return [
    'displayErrorDetails' => true,

    // une factory pour instancier la connexion PDO.
    'pdo.praticien' => function () {
        $dsn = sprintf(
            '%s:host=%s;dbname=%s',
            $_ENV['prat.driver'],
            $_ENV['prat.host'],
            $_ENV['prat.database']
        );
        
        $pdo = new \PDO($dsn, $_ENV['prat.username'], $_ENV['prat.password']);
        $pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
        $pdo->setAttribute(\PDO::ATTR_DEFAULT_FETCH_MODE, \PDO::FETCH_ASSOC);
        
        return $pdo;
    },
];