<?php
namespace toubilib\core\application\usecases;
use toubilib\core\domain\entities\Patient;

interface ServicePatientInterface
{
    public function existePatient(string $patientId): bool;
    public function consulterPatient(string $id): ?Patient;
}
