<?php
namespace toubilib\core\application\ports;

use toubilib\core\domain\entities\Auth;

interface AuthRepositoryInterface
{
    public function findByEmail(string $authEmail): ?Auth;
    public function save(Auth $auth): void;
}