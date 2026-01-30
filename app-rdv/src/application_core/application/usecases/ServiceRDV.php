<?php
namespace toubilib\core\application\usecases;

use toubilib\core\application\ports\RDVRepositoryInterface;
use toubilib\core\application\ports\PraticienInfoPort;
use toubilib\core\application\dto\InputRDVDTO;
use toubilib\core\domain\entities\RDV;
use toubilib\core\application\dto\RDVDTO;
use toubilib\core\domain\exceptions\PraticienOccupeException;
use toubilib\core\domain\exceptions\RessourceInexistanteException;
use toubilib\core\domain\exceptions\MotifVisiteInvalideException;
use toubilib\core\domain\exceptions\CreneauInvalideException;
use toubilib\core\domain\ports\EventPublisherInterface;
use DateTime;
use Ramsey\Uuid\Uuid;

class ServiceRDV implements ServiceRDVInterface
{
    private RDVRepositoryInterface $rdvRepository;
    private PraticienInfoPort $praticienService;
    private ServicePatientInterface $servicePatient;
    private EventPublisherInterface $eventPublisher;

    public function __construct(
        RDVRepositoryInterface $rdvRepository,
        PraticienInfoPort $praticienService,
        ServicePatientInterface $servicePatient,
        EventPublisherInterface $eventPublisher
    ) {
        $this->rdvRepository = $rdvRepository;
        $this->praticienService = $praticienService;
        $this->servicePatient = $servicePatient;
        $this->eventPublisher = $eventPublisher;
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

        // Vérifier que le praticien existe via l'adaptateur HTTP
        if (!$this->praticienService->existePraticien($dto->praticienId)) {
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

        // Vérifier les motifs autorisés via l'adaptateur HTTP
        $motifsAutorises = $this->praticienService->getMotifsVisite($dto->praticienId);
        if (!in_array($dto->motifVisite, $motifsAutorises, true)) {
            throw new MotifVisiteInvalideException();
        }

        $jour = (int)$debut->format('N');
        $heure = (int)$debut->format('H');
        if ($jour > 5 || $heure < 8 || $heure >= 19) {
            throw new CreneauInvalideException("Créneau horaire invalide (lun-ven 08:00-19:00)");
        }

        // Vérifier les créneaux occupés
        $creneauxOccupes = $this->rdvRepository->findBusySlots($dto->praticienId, $debut, $fin);
        foreach ($creneauxOccupes as $existing) {
            if ($existing->getDateHeureDebut() < $fin && $existing->getDateHeureFin() > $debut) {
                throw new PraticienOccupeException();
            }
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

        $this->rdvRepository->save($rdv);

        // Publier événements pour patient et praticien
        $patientInfo = $this->servicePatient->consulterPatient($dto->patientId);
        $praticienInfo = $this->praticienService->getPraticien($dto->praticienId);

        // Événement pour le patient
        $this->eventPublisher->publish('rdv.created.patient', [
            'eventType' => 'rdv.created.patient',
            'rdvId' => $rdv->getId(),
            'recipient' => [
                'type' => 'patient',
                'id' => $patientInfo->getId(),
                'email' => $patientInfo->getEmail(),
                'nom' => $patientInfo->getNom(),
                'prenom' => $patientInfo->getPrenom()
            ],
            'data' => [
                'dateHeureDebut' => $rdv->getDateHeureDebut()->format('Y-m-d H:i:s'),
                'duree' => $rdv->getDuree(),
                'motifVisite' => $rdv->getMotifVisite(),
                'praticien' => [
                    'nom' => $praticienInfo['nom'],
                    'prenom' => $praticienInfo['prenom'],
                    'specialite' => $praticienInfo['specialite']
                ]
            ],
            'timestamp' => date('c')
        ]);

        // Événement pour le praticien
        $this->eventPublisher->publish('rdv.created.praticien', [
            'eventType' => 'rdv.created.praticien',
            'rdvId' => $rdv->getId(),
            'recipient' => [
                'type' => 'praticien',
                'id' => $praticienInfo['id'],
                'email' => $praticienInfo['email'],
                'nom' => $praticienInfo['nom'],
                'prenom' => $praticienInfo['prenom']
            ],
            'data' => [
                'dateHeureDebut' => $rdv->getDateHeureDebut()->format('Y-m-d H:i:s'),
                'duree' => $rdv->getDuree(),
                'motifVisite' => $rdv->getMotifVisite(),
                'patient' => [
                    'nom' => $patientInfo->getNom(),
                    'prenom' => $patientInfo->getPrenom()
                ]
            ],
            'timestamp' => date('c')
        ]);

        return $rdv;
    }

    public function annulerRendezVous(string $rdvId): void
    {
        $rdv = $this->rdvRepository->findById($rdvId);
        if (!$rdv) {
            throw new RessourceInexistanteException("RDV");
        }

        $rdv->annuler();

        $this->rdvRepository->updateStatus($rdv->getId(), $rdv->getStatus());

        // Publier événements d'annulation pour patient et praticien
        $patientInfo = $this->servicePatient->consulterPatient($rdv->getPatientId());
        $praticienInfo = $this->praticienService->getPraticien($rdv->getPraticienId());

        // Événement pour le patient
        $this->eventPublisher->publish('rdv.cancelled.patient', [
            'eventType' => 'rdv.cancelled.patient',
            'rdvId' => $rdv->getId(),
            'recipient' => [
                'type' => 'patient',
                'id' => $patientInfo->getId(),
                'email' => $patientInfo->getEmail(),
                'nom' => $patientInfo->getNom(),
                'prenom' => $patientInfo->getPrenom()
            ],
            'data' => [
                'dateHeureDebut' => $rdv->getDateHeureDebut()->format('Y-m-d H:i:s'),
                'duree' => $rdv->getDuree(),
                'motifVisite' => $rdv->getMotifVisite(),
                'praticien' => [
                    'nom' => $praticienInfo['nom'],
                    'prenom' => $praticienInfo['prenom'],
                    'specialite' => $praticienInfo['specialite']
                ]
            ],
            'timestamp' => date('c')
        ]);

        // Événement pour le praticien
        $this->eventPublisher->publish('rdv.cancelled.praticien', [
            'eventType' => 'rdv.cancelled.praticien',
            'rdvId' => $rdv->getId(),
            'recipient' => [
                'type' => 'praticien',
                'id' => $praticienInfo['id'],
                'email' => $praticienInfo['email'],
                'nom' => $praticienInfo['nom'],
                'prenom' => $praticienInfo['prenom']
            ],
            'data' => [
                'dateHeureDebut' => $rdv->getDateHeureDebut()->format('Y-m-d H:i:s'),
                'duree' => $rdv->getDuree(),
                'motifVisite' => $rdv->getMotifVisite(),
                'patient' => [
                    'nom' => $patientInfo->getNom(),
                    'prenom' => $patientInfo->getPrenom()
                ]
            ],
            'timestamp' => date('c')
        ]);
    }

    public function marquerCommeHonore(string $rdvId): void
    {
        $rdv = $this->rdvRepository->findById($rdvId);
        if (!$rdv) {
            throw new RessourceInexistanteException("RDV");
        }

        $rdv->honorer();

        $this->rdvRepository->updateStatus($rdv->getId(), $rdv->getStatus());
    }

    public function marquerCommeNonHonore(string $rdvId): void
    {
        $rdv = $this->rdvRepository->findById($rdvId);
        if (!$rdv) {
            throw new RessourceInexistanteException("RDV");
        }

        $rdv->nonHonorer();

        $this->rdvRepository->updateStatus($rdv->getId(), $rdv->getStatus());
    }

    public function getAgendaPraticien(string $praticienId, ?\DateTime $dateDebut = null, ?\DateTime $dateFin = null): array
    {
        $dateDebut = $dateDebut ?? new \DateTime();
        $dateFin = $dateFin ?? (clone $dateDebut)->modify('+1 day');

        $rdvs = $this->rdvRepository->findBusySlots($praticienId, $dateDebut, $dateFin);

        $agenda = [];
        foreach ($rdvs as $rdv) {
            $agenda[] = [
                'id' => $rdv->getId(),
                'patientId' => $rdv->getPatientId(),
                'patientLink' => "/patients/{$rdv->getPatientId()}",
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