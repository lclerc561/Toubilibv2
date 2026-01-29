<?php
namespace toubilib\core\domain\entities;

class Patient
{
    private string $id;
    private string $nom;
    private string $prenom;
    private ?\DateTime $dateNaissance;
    private ?string $adresse;
    private ?string $codePostal;
    private ?string $ville;
    private ?string $email;
    private string $telephone;

    public function __construct(
        string $id,
        string $nom,
        string $prenom,
        ?\DateTime $dateNaissance = null,
        ?string $adresse = null,
        ?string $codePostal = null,
        ?string $ville = null,
        ?string $email = null,
        string $telephone = ''
    ) {
        $this->id = $id;
        $this->nom = $nom;
        $this->prenom = $prenom;
        $this->dateNaissance = $dateNaissance;
        $this->adresse = $adresse;
        $this->codePostal = $codePostal;
        $this->ville = $ville;
        $this->email = $email;
        $this->telephone = $telephone;
    }

    public function getId(): string { return $this->id; }
    public function getNom(): string { return $this->nom; }
    public function getPrenom(): string { return $this->prenom; }
    public function getDateNaissance(): ?\DateTime { return $this->dateNaissance; }
    public function getEmail(): ?string { return $this->email; }
    public function getTelephone(): string { return $this->telephone; }
    public function getAdresse(): ?string {return $this->adresse;}
    public function getCodePostal(): ?string {return $this->codePostal;}
    public function getVille(): ?string{return $this->ville;}

}
