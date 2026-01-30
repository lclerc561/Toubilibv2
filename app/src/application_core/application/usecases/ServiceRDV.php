<?php
namespace toubilib\core\application\usecases;

use toubilib\core\application\ports\RDVRepositoryInterface;
use toubilib\core\application\dto\InputRDVDTO;
use toubilib\core\domain\entities\RDV;
use toubilib\core\application\dto\RDVDTO;
use toubilib\core\application\usecases\ServiceIndisponibiliteInterface;
use toubilib\core\domain\exceptions\PraticienOccupeException;
use toubilib\core\domain\exceptions\PraticienIndisponibleException;
use toubilib\core\domain\exceptions\RessourceInexistanteException;
use toubilib\core\domain\exceptions\MotifVisiteInvalideException;
use toubilib\core\domain\exceptions\CreneauInvalideException;
use DateTime;
use Exception;
use Ramsey\Uuid\Uuid;

class ServiceRDV implements ServiceRDVInterface
{
    private RDVRepositoryInterface $rdvRepository;
    private ServicePraticienInterface $servicePraticien;
    private ServicePatientInterface $servicePatient;
    private ServiceIndisponibiliteInterface $serviceIndisponibilite;

    public function __construct(
        RDVRepositoryInterface $rdvRepository,
        ServicePraticienInterface $servicePraticien,
        ServicePatientInterface $servicePatient,
        ServiceIndisponibiliteInterface $serviceIndisponibilite
    ) {
        $this->rdvRepository = $rdvRepository;
        $this->servicePraticien = $servicePraticien;
        $this->servicePatient = $servicePatient;
        $this->serviceIndisponibilite = $serviceIndisponibilite;
    }

    public function listerCreneauxOccupes(string $praticienId, DateTime $debut, DateTime $fin): array
    {
        $rdvs = $this->rdvRepository->findBusySlots($praticienId, $debut, $fin);
        
        $dtos = [];
        foreach ($rdvs as $rdv) {
            $dtos[] = new RDVDTO(
                $rdv->getId(),
                $rdv->getPraticienId(),
                $rdv->getPatientId(),
                $rdv->getPatientEmail(),
                $rdv->getDateHeureDebut()->format('Y-m-d H:i:s'),
                $rdv->getDateHeureFin()?->format('Y-m-d H:i:s'),
                $rdv->getStatus(),
                $rdv->getDuree(),
                $rdv->getDateCreation()?->format('Y-m-d H:i:s'),
                $rdv->getMotifVisite()
            );
        }
        return $dtos;
    }

    public function consulterRdv(string $rdvId): ?RDVDTO
    {
        $rdv = $this->rdvRepository->findById($rdvId);

        if (!$rdv) {
            return null;
        }

        return new RDVDTO(
            $rdv->getId(),
            $rdv->getPraticienId(),
            $rdv->getPatientId(),
            $rdv->getPatientEmail(),
            $rdv->getDateHeureDebut()->format('Y-m-d H:i:s'),
            $rdv->getDateHeureFin()?->format('Y-m-d H:i:s'),
            $rdv->getStatus(),
            $rdv->getDuree(),
            $rdv->getDateCreation()?->format('Y-m-d H:i:s'),
            $rdv->getMotifVisite()
        );
    }

    public function creerRendezVous(InputRDVDTO $dto): RDV
    {
        $debut = new DateTime($dto->dateHeureDebut);
        $fin = (clone $debut)->modify("+{$dto->duree} minutes");

        if (!$this->servicePraticien->RecherchePraticienByID($dto->praticienId)) {
            throw new RessourceInexistanteException("Praticien", $dto->praticienId);
        }

        if (!$this->servicePatient->existePatient($dto->patientId)) {
            throw new RessourceInexistanteException("Patient", $dto->patientId);
        }

        // Vérifier que la date n'est pas dans le passé
        $now = new DateTime();
        if ($debut < $now) {
            throw new CreneauInvalideException("Impossible de créer un rendez-vous dans le passé");
        }

        $motifsAutorises = $this->servicePraticien->getMotifsVisite($dto->praticienId);
        if (!in_array($dto->motifVisite, $motifsAutorises, true)) {
            throw new MotifVisiteInvalideException();
        }

        $jour = (int)$debut->format('N');
        $heure = (int)$debut->format('H');
        if ($jour > 5 || $heure < 8 || $heure >= 19) {
            throw new CreneauInvalideException("Créneau horaire invalide (lun-ven 08:00-19:00)");
        }

        // Vérifier les créneaux occupés (RDV existants)
        $creneauxOccupes = $this->rdvRepository->findBusySlots($dto->praticienId, $debut, $fin);
        foreach ($creneauxOccupes as $existing) {
            if ($existing->getDateHeureDebut() < $fin && $existing->getDateHeureFin() > $debut) {
                throw new PraticienOccupeException();
            }
        }

        // Vérifier les indisponibilités
        $indisponibilites = $this->serviceIndisponibilite->trouverIndisponibilitesChevauchantes($dto->praticienId, $debut, $fin);
        if (!empty($indisponibilites)) {
            throw new PraticienIndisponibleException();
        }

        $rdv = new RDV(
            Uuid::uuid4()->toString(),
            $dto->praticienId,
            $dto->patientId,
            $dto->patientEmail ?? null,
            $debut,
            $fin,
            0,
            $dto->duree,
            new DateTime(),
            $dto->motifVisite
        );

        // persist
        $this->rdvRepository->save($rdv);

        return $rdv;
    }

    public function annulerRendezVous(string $rdvId): void
    {
        $rdv = $this->rdvRepository->findById($rdvId);
        if (!$rdv) {
            throw new RessourceInexistanteException("RDV", $rdvId);
        }

        $rdv->annuler(); // méthode métier sur l'entité RDV, change le status 

        $this->rdvRepository->updateStatus($rdv->getId(), $rdv->getStatus());
    }

    public function marquerCommeHonore(string $rdvId): void
    {
        $rdv = $this->rdvRepository->findById($rdvId);
        if (!$rdv) {
            throw new RessourceInexistanteException("RDV");
        }

        $rdv->honorer(); // méthode métier sur l'entité RDV, change le status à 2

        $this->rdvRepository->updateStatus($rdv->getId(), $rdv->getStatus());
    }

    public function marquerCommeNonHonore(string $rdvId): void
    {
        $rdv = $this->rdvRepository->findById($rdvId);
        if (!$rdv) {
            throw new RessourceInexistanteException("RDV");
        }

        $rdv->nonHonorer(); // méthode métier sur l'entité RDV, change le status à 3

        $this->rdvRepository->updateStatus($rdv->getId(), $rdv->getStatus());
    }

    public function getAgendaPraticien(string $praticienId, ?\DateTime $dateDebut = null, ?\DateTime $dateFin = null): array
    {
        $dateDebut = $dateDebut ?? new \DateTime(); // par défaut aujourd'hui
        $dateFin = $dateFin ?? (clone $dateDebut)->modify('+1 day'); // par défaut demain

        $rdvs = $this->rdvRepository->findBusySlots($praticienId, $dateDebut, $dateFin);

        $agenda = [];
        foreach ($rdvs as $rdv) {
            $agenda[] = [
                'id' => $rdv->getId(),
                'patientId' => $rdv->getPatientId(),
                'patientLink' => "/patients/{$rdv->getPatientId()}", // lien vers le patient
                'dateHeureDebut' => $rdv->getDateHeureDebut()->format('Y-m-d H:i:s'),
                'dateHeureFin' => $rdv->getDateHeureFin()?->format('Y-m-d H:i:s'),
                'duree' => $rdv->getDuree(),
                'motifVisite' => $rdv->getMotifVisite(),
                'status' => $rdv->getStatus(),
            ];
        }

        return $agenda;
    }

    public function listerConsultationsPatient(string $id): array
    {
        $consultations = $this->rdvRepository->findConsultationsByPatientId($id);
        $praticienDTOs = [];
        foreach ($consultations as $consultation) {
            $praticienDTOs[] = new RDVDTO(
                $consultation->getId(),
                $consultation->getPraticienId(),
                $consultation->getPatientId(),
                $consultation->getPatientEmail(),
                $consultation->getDateHeureDebut()->format('Y-m-d H:i:s'),
                $consultation->getDateHeureFin()?->format('Y-m-d H:i:s'),
                $consultation->getStatus(),
                $consultation->getDuree(),
                $consultation->getDateCreation()?->format('Y-m-d H:i:s'),
                $consultation->getMotifVisite()
            );
        }
        return $praticienDTOs;
    }

}
