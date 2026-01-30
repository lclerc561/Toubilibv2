<?php
namespace toubilib\core\application\usecases;

use toubilib\core\application\ports\PatientRepositoryInterface;
use toubilib\core\domain\entities\Patient;

class ServicePatient implements ServicePatientInterface
{
    private PatientRepositoryInterface $patientRepository;

    public function __construct(PatientRepositoryInterface $patientRepository)
    {
        $this->patientRepository = $patientRepository;
    }

    public function existePatient(string $patientId): bool
    {
        return $this->patientRepository->findById($patientId) !== null;
    }
    public function consulterPatient(string $id): ?Patient
    {
        return $this->patientRepository->findById($id);
    }

}
