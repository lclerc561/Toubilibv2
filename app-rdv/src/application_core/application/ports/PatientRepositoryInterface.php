<?php
namespace toubilib\core\application\ports;

use toubilib\core\domain\entities\Patient;

interface PatientRepositoryInterface
{
    public function findById(string $patientId): ?Patient;
    public function save(Patient $patient): void;
}
