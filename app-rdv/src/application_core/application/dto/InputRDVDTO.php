<?php
namespace toubilib\core\application\dto;

class InputRDVDTO
{
    public string $praticienId;
    public string $patientId;
    public ?string $patientEmail;
    public string $dateHeureDebut;
    public int $duree;
    public ?string $motifVisite;

    public function __construct(
        string $praticienId,
        string $patientId,
        ?string $patientEmail,
        string $dateHeureDebut,
        int $duree,
        ?string $motifVisite
    ) {
        $this->praticienId = $praticienId;
        $this->patientId = $patientId;
        $this->patientEmail = $patientEmail;
        $this->dateHeureDebut = $dateHeureDebut;
        $this->duree = $duree;
        $this->motifVisite = $motifVisite;
    }
}
