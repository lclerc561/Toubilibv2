<?php
namespace toubilib\infra\repositories;

use PDO;
use toubilib\core\application\ports\PatientRepositoryInterface;
use toubilib\core\domain\entities\Patient;

class PDOPatientRepository implements PatientRepositoryInterface
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function findById(string $patientId): ?Patient
    {
        $stmt = $this->pdo->prepare("SELECT * FROM patient WHERE id = :id");
        $stmt->execute(['id' => $patientId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$row) {
            return null;
        }
        return new Patient(
            $row['id'],
            $row['nom'],
            $row['prenom'],
            isset($row['date_naissance']) ? new \DateTime($row['date_naissance']) : null,
            $row['adresse'] ?? null,
            $row['code_postal'] ?? null,
            $row['ville'] ?? null,
            $row['email'] ?? null,
            $row['telephone']
        );
    }

    public function save(Patient $patient): void
    {
        $stmt = $this->pdo->prepare(
            "INSERT INTO patient (id, nom, prenom, date_naissance, adresse, code_postal, ville, email, telephone) 
             VALUES (:id, :nom, :prenom, :date_naissance, :adresse, :code_postal, :ville, :email, :telephone)"
        );
        $stmt->execute([
            'id' => $patient->getId(),
            'nom' => $patient->getNom(),
            'prenom' => $patient->getPrenom(),
            'date_naissance' => $patient->getDateNaissance()?->format('Y-m-d'),
            'adresse' => $patient->getAdresse(),
            'code_postal' => $patient->getCodePostal(),
            'ville' => $patient->getVille(),
            'email' => $patient->getEmail(),
            'telephone' => $patient->getTelephone()
        ]);
    }
}