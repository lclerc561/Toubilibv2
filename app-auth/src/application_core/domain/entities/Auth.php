<?php
namespace toubilib\core\domain\entities;

class Auth{
    private string $id;
    private string $email;
    private string $mdp;
    private int $role;

    public function __construct(
        string $id,
        string $email,
        string $mdp,
        int $role
    ){
        $this->id=$id;
        $this->email=$email;
        $this->mdp=$mdp;
        $this->role=$role;
    }

    public function getId(): string { return $this->id; }
    public function getEmail(): string { return $this->email; }
    public function getMdp(): string { return $this->mdp; }
    public function getRole(): int { return $this->role; }
}