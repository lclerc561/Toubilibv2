<?php
namespace toubilib\core\application\ports;

//Rôle : Définir le contrat que tout provider d'authentification doit respecter.
interface AuthProviderInterface
{
    public function validateAndExtractUserData(string $token): array;
    public function isTokenValid(string $token): bool;
}