<?php

namespace toubilib\core\domain\entities;
use DateTime;
use Exception;

class RDV
{
    public function __construct(
        private string $id,
        private string $praticienId,
        private string $patientId,
        private ?string $patientEmail,
        private \DateTime $dateHeureDebut,
        private ?\DateTime $dateHeureFin,
        private int $status,
        private int $duree,
        private ?\DateTime $dateCreation,
        private ?string $motifVisite
    ) {}

    public function getId(): string { return $this->id; }
    public function getPraticienId(): string { return $this->praticienId; }
    public function getPatientId(): string { return $this->patientId; }
    public function getPatientEmail(): ?string { return $this->patientEmail; }
    public function getDateHeureDebut(): \DateTime { return $this->dateHeureDebut; }
    public function getDateHeureFin(): ?\DateTime { return $this->dateHeureFin; }
    public function getStatus(): int { return $this->status; }
    public function getDuree(): int { return $this->duree; }
    public function getDateCreation(): ?\DateTime { return $this->dateCreation; }
    public function getMotifVisite(): ?string { return $this->motifVisite; }

    public function annuler(): void
    {
        $now = new DateTime('now');

        if ($this->status === 1) {
            throw new Exception("Rendez-vous déjà annulé");
        }

        // Comparer seulement la date (sans l'heure) pour éviter les problèmes de fuseau horaire
        $dateRDV = $this->dateHeureDebut->format('Y-m-d');
        $dateNow = $now->format('Y-m-d');
        
        if ($dateRDV < $dateNow) {
            throw new Exception("Impossible d'annuler un rendez-vous passé");
        }

        $this->status = 1;
    }

    public function honorer(): void
    {
        if ($this->status === 1) {
            throw new Exception("Impossible de marquer un rendez-vous annulé comme honoré");
        }

        if ($this->status === 2) {
            throw new Exception("Rendez-vous déjà marqué comme honoré");
        }

        if ($this->status === 3) {
            throw new Exception("Impossible de marquer un rendez-vous non honoré comme honoré");
        }

        $now = new DateTime('now');
        
        // On peut marquer comme honoré seulement si le RDV est passé
        if ($this->dateHeureDebut > $now) {
            throw new Exception("Impossible de marquer un rendez-vous futur comme honoré");
        }

        $this->status = 2;
    }

    public function nonHonorer(): void
    {
        if ($this->status === 1) {
            throw new Exception("Impossible de marquer un rendez-vous annulé comme non honoré");
        }

        if ($this->status === 2) {
            throw new Exception("Impossible de marquer un rendez-vous honoré comme non honoré");
        }

        if ($this->status === 3) {
            throw new Exception("Rendez-vous déjà marqué comme non honoré");
        }

        $now = new DateTime('now');
        
        // On peut marquer comme non honoré seulement si le RDV est passé
        if ($this->dateHeureDebut > $now) {
            throw new Exception("Impossible de marquer un rendez-vous futur comme non honoré");
        }

        $this->status = 3;
    }
}
