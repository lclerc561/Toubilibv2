<?php
namespace toubilib\core\application\dto;

class RDVDTO
{
    public function __construct(
        public string $id,
        public string $praticienId,
        public string $patientId,
        public ?string $patientEmail,
        public string $dateHeureDebut,
        public ?string $dateHeureFin,
        public int $status,
        public int $duree,
        public ?string $dateCreation,
        public ?string $motifVisite
    ) {}
}