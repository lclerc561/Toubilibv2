<?php
namespace toubilib\infra\repositories;

use toubilib\core\application\ports\RDVRepositoryInterface;
use toubilib\core\domain\entities\RDV;
use DateTime;

class PDORDVRepository implements RDVRepositoryInterface
{
    private \PDO $pdo;

    public function __construct(\PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function findBusySlots(string $praticienId, DateTime $debut, DateTime $fin): array
    {
        $sql = "SELECT id, praticien_id, patient_id, patient_email, date_heure_debut, 
                       date_heure_fin, status, duree, date_creation, motif_visite
                FROM rdv
                WHERE praticien_id = :pid
                  AND DATE(date_heure_debut) BETWEEN :debut AND :fin
                  AND status = 0
                ORDER BY date_heure_debut ASC";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            'pid' => $praticienId,
            'debut' => $debut->format('Y-m-d'),
            'fin' => $fin->format('Y-m-d'),
        ]);

        $rows = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        $rdvs = [];
        foreach ($rows as $row) {
            $rdvs[] = new RDV(
                $row['id'],
                $row['praticien_id'],
                $row['patient_id'],
                $row['patient_email'] ?? null,
                new DateTime($row['date_heure_debut']),
                isset($row['date_heure_fin']) ? new DateTime($row['date_heure_fin']) : null,
                (int)$row['status'],
                (int)$row['duree'],
                isset($row['date_creation']) ? new DateTime($row['date_creation']) : null,
                $row['motif_visite'] ?? null
            );
        }
        return $rdvs;
    }

    public function findById(string $rdvId): ?RDV
    {
        $sql = "SELECT * FROM rdv WHERE id = :id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['id' => $rdvId]);
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);
        if (!$row) return null;

        return new RDV(
            $row['id'],
            $row['praticien_id'],
            $row['patient_id'],
            $row['patient_email'] ?? null,
            new DateTime($row['date_heure_debut']),
            isset($row['date_heure_fin']) ? new DateTime($row['date_heure_fin']) : null,
            (int)$row['status'],
            (int)$row['duree'],
            isset($row['date_creation']) ? new DateTime($row['date_creation']) : null,
            $row['motif_visite'] ?? null
        );
    }

    public function save(RDV $rdv): void
    {
        $sql = "INSERT INTO rdv (id, praticien_id, patient_id, patient_email, date_heure_debut, date_heure_fin, status, duree, date_creation, motif_visite)
                VALUES (:id, :pid, :patientId, :patientEmail, :debut, :fin, :status, :duree, :dateCreation, :motif)";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            'id' => $rdv->getId(),
            'pid' => $rdv->getPraticienId(),
            'patientId' => $rdv->getPatientId(),
            'patientEmail' => $rdv->getPatientEmail(),
            'debut' => $rdv->getDateHeureDebut()->format('Y-m-d H:i:s'),
            'fin' => $rdv->getDateHeureFin() ? $rdv->getDateHeureFin()->format('Y-m-d H:i:s') : null,
            'status' => $rdv->getStatus(),
            'duree' => $rdv->getDuree(),
            'dateCreation' => $rdv->getDateCreation() ? $rdv->getDateCreation()->format('Y-m-d H:i:s') : null,
            'motif' => $rdv->getMotifVisite()
        ]);
    }

    public function updateStatus(string $rdvId, int $status): void
    {
        $sql = "UPDATE rdv SET status = :status WHERE id = :id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            'id' => $rdvId,
            'status' => $status
        ]);
    }

    public function findConsultationsByPatientId(string $patientId): array
    {
        $stmt = $this->pdo->prepare("SELECT * FROM rdv WHERE patient_id = :patient_id ORDER BY date_heure_debut DESC");
        $stmt->execute(['patient_id' => $patientId]);
        $consultations = [];
        while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
            $rdv = new RDV(
            $row['id'],
            $row['praticien_id'],
            $row['patient_id'],
            $row['patient_email'] ?? null,
            new DateTime($row['date_heure_debut']),
            isset($row['date_heure_fin']) ? new DateTime($row['date_heure_fin']) : null,
            (int)$row['status'],
            (int)$row['duree'],
            isset($row['date_creation']) ? new DateTime($row['date_creation']) : null,
            $row['motif_visite'] ?? null
        );
            $consultations[] = $rdv; 
        }
        return $consultations;
    }

}
