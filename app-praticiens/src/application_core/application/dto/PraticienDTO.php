<?php

namespace toubilib\core\application\dto;

use toubilib\core\domain\entities\praticien\Praticien;

class PraticienDTO
{
    public string $id;
    public string $nom;
    public string $prenom;
    public string $ville;
    public string $email;
    public string $specialite;
    public string $telephone;
    public ?string $structureNom;
    public ?string $adresse;
    public ?string $codePostal;
    public ?string $structureVille;
    public ?string $rppsId;
    public string $titre;
    public bool $accepteNouveauPatient;
    public bool $estOrganisation;
    public array $motifsVisite;
    public array $moyensPaiement;

    public function __construct(Praticien $praticien, array $motifsVisite = [], array $moyensPaiement = [])
    {
        $this->id = $praticien->id;
        $this->nom = $praticien->nom;
        $this->prenom = $praticien->prenom;
        $this->ville = $praticien->ville;
        $this->email = $praticien->email;
        $this->specialite = $praticien->specialite->libelle;
        $this->telephone = $praticien->telephone;
        $this->structureNom = $praticien->structureNom;
        $this->adresse = $praticien->adresse;
        $this->codePostal = $praticien->codePostal;
        $this->structureVille = $praticien->structureVille;
        $this->rppsId = $praticien->rppsId;
        $this->titre = $praticien->titre;
        $this->accepteNouveauPatient = $praticien->accepteNouveauPatient;
        $this->estOrganisation = $praticien->estOrganisation;
        $this->motifsVisite = $motifsVisite;
        $this->moyensPaiement = $moyensPaiement;
    }
}