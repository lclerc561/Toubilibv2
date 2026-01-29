<?php
namespace toubilib\core\application\dto;

class InputAuthDTO
{
    public string $email;
    public string $mdp;

    public function __construct(
        string $email,
        string $mdp
    ) {
        $this->email = $email;
        $this->mdp = $mdp;
    }
}