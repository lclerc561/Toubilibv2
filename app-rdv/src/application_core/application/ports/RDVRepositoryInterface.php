<?php
namespace toubilib\core\application\ports;

use toubilib\core\domain\entities\RDV;

interface RDVRepositoryInterface
{
    public function findBusySlots(string $praticienId, \DateTime $debut, \DateTime $fin): array;
    public function findById(string $rdvId): ?RDV;
    public function save(RDV $rdv): void;
    public function updateStatus(string $rdvId, int $status): void;
    public function findConsultationsByPatientId(string $patientId): array;
}