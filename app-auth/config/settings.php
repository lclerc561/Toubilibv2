<?php

return [
    'displayErrorDetails' => true,
    'logs.dir' => __DIR__ . '/../var/logs',

    // Configuration directe pour PostgreSQL
    'toubiauth.db.config' => [
        'driver'   => 'pgsql',
        'host'     => 'toubiauth.db', // Nom du service dans ton docker-compose.yml
        'database' => 'toubiauth',    // Correspond à POSTGRES_DB
        'username' => 'toubiauth',    // Correspond à POSTGRES_USER
        'password' => 'toubiauth',    // Correspond à POSTGRES_PASSWORD
        'port'     => 5432,           // Port interne standard de Postgres
        'charset'  => 'utf8'
    ],
];