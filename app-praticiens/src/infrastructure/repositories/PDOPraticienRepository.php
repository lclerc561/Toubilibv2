<?php

namespace toubilib\infra\repositories;

use toubilib\core\domain\entities\praticien\Praticien;
use toubilib\core\domain\entities\praticien\Specialite;
use toubilib\core\application\ports\PraticienRepositoryInterface;

class PDOPraticienRepository implements PraticienRepositoryInterface
{
    private \PDO $pdo;

    public function __construct(\PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    /**
     * Convertit une valeur bit(1) PostgreSQL en booléen PHP
     */
    private function toBool($value): bool
    {
        if (is_bool($value)) {
            return $value;
        }
        if (is_string($value)) {
            return $value === '1' || $value === 't' || strtolower($value) === 'true';
        }
        return (bool)$value;
    }

    public function findAll(): array
    {
        $sql = "SELECT p.id, p.nom, p.prenom, p.ville, p.email, p.telephone, 
                       p.rpps_id, p.titre, p.nouveau_patient, p.organisation,
                       s.id as specialite_id, s.libelle as specialite_libelle, s.description as specialite_description
                FROM praticien p
                JOIN specialite s ON p.specialite_id = s.id";
        
        $stmt = $this->pdo->query($sql);
        $praticiens = [];
        while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
            $specialite = new Specialite($row['specialite_id'], $row['specialite_libelle'], $row['specialite_description'] ?? null);
            $praticiens[] = new Praticien(
                $row['id'],
                $row['nom'],
                $row['prenom'],
                $row['ville'],
                $row['email'],
                $specialite,
                $row['telephone'],
                null, // structureNom
                null, // adresse
                null, // codePostal
                null, // structureVille
                $row['rpps_id'] ?? null,
                $row['titre'] ?? 'Dr.',
                $this->toBool($row['nouveau_patient'] ?? true),
                $this->toBool($row['organisation'] ?? false)
            );
        }
        return $praticiens;
    }

    public function findById(string $id): ?Praticien
    {
        $sql = "SELECT p.id, p.nom, p.prenom, p.ville, p.email, p.telephone, 
                       p.rpps_id, p.titre, p.nouveau_patient, p.organisation,
                       s.id as specialite_id, s.libelle as specialite_libelle, s.description as specialite_description,
                       st.nom as structure_nom, st.adresse, st.code_postal, st.ville as structure_ville
                FROM praticien p
                JOIN specialite s ON p.specialite_id = s.id
                LEFT JOIN structure st ON p.structure_id = st.id
                WHERE p.id = :id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);

        if ($row === false) {
            return null;
        }
        
        $specialite = new Specialite($row['specialite_id'], $row['specialite_libelle'], $row['specialite_description'] ?? null);
        $praticien = new Praticien(
            $row['id'],
            $row['nom'],
            $row['prenom'],
            $row['ville'],
            $row['email'],
            $specialite,
            $row['telephone'],
            $row['structure_nom'],
            $row['adresse'],
            $row['code_postal'],
            $row['structure_ville'],
            $row['rpps_id'] ?? null,
            $row['titre'] ?? 'Dr.',
            $this->toBool($row['nouveau_patient'] ?? true),
            $this->toBool($row['organisation'] ?? false)
        );
        return $praticien;
    }

    public function findBySpecialiteAndVille(?string $specialiteLibelle, ?string $ville): array
    {
        $sql = "SELECT p.id, p.nom, p.prenom, p.ville, p.email, p.telephone, 
                       p.rpps_id, p.titre, p.nouveau_patient, p.organisation,
                       s.id as specialite_id, s.libelle as specialite_libelle, s.description as specialite_description
                FROM praticien p
                JOIN specialite s ON p.specialite_id = s.id";
        $conditions = [];
        $params = [];

        if ($specialiteLibelle !== null && $specialiteLibelle !== '') {
            $conditions[] = "s.libelle ILIKE :spec";
            $params['spec'] = '%' . $specialiteLibelle . '%';
        }

        if ($ville !== null && $ville !== '') {
            $conditions[] = "p.ville ILIKE :ville";
            $params['ville'] = '%' . $ville . '%';
        }

        if (!empty($conditions)) {
            $sql .= ' WHERE ' . implode(' AND ', $conditions);
        }

        $sql .= " ORDER BY p.nom, p.prenom";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        $rows = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        $praticiens = [];
        foreach ($rows as $row) {
            $specialite = new Specialite($row['specialite_id'], $row['specialite_libelle'], $row['specialite_description'] ?? null);
            $praticiens[] = new Praticien(
                $row['id'],
                $row['nom'],
                $row['prenom'],
                $row['ville'],
                $row['email'],
                $specialite,
                $row['telephone'],
                null, // structureNom
                null, // adresse
                null, // codePostal
                null, // structureVille
                $row['rpps_id'] ?? null,
                $row['titre'] ?? 'Dr.',
                $this->toBool($row['nouveau_patient'] ?? true),
                $this->toBool($row['organisation'] ?? false)
            );
        }
        return $praticiens;
    }

    public function getMotifsVisite(string $praticienId): array
    {
        $sql = "SELECT mv.libelle
                FROM motif_visite mv
                JOIN praticien2motif pm ON mv.id = pm.motif_id
                WHERE pm.praticien_id = :pid";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['pid' => $praticienId]);
        $rows = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        $motifs = [];
        foreach ($rows as $row) {
            $motifs[] = $row['libelle'];
        }

        return $motifs;
    }

    public function getMoyensPaiement(string $praticienId): array
    {
        $sql = "SELECT mp.libelle
                FROM moyen_paiement mp
                JOIN praticien2moyen pm ON mp.id = pm.moyen_id
                WHERE pm.praticien_id = :pid";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['pid' => $praticienId]);
        $rows = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        $moyens = [];
        foreach ($rows as $row) {
            $moyens[] = $row['libelle'];
        }

        return $moyens;
    }
}