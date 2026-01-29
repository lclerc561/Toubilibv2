<?php

namespace toubilib\core\application\usecases;

use toubilib\core\application\dto\PraticienDTO;
use toubilib\core\application\ports\PraticienRepositoryInterface;

class ServicePraticien implements ServicePraticienInterface
{
    private PraticienRepositoryInterface $praticienRepository;

    public function __construct(PraticienRepositoryInterface $praticienRepository)
    {
        $this->praticienRepository = $praticienRepository;
    }

    public function listerPraticiens(): array
    {
        $praticiens = $this->praticienRepository->findAll();
        $praticienDTOs = [];
        foreach ($praticiens as $praticien) {
            $praticienDTOs[] = new PraticienDTO($praticien);
        }
        return $praticienDTOs;
    }

    public function RecherchePraticienByID(string $id): ?PraticienDTO
    {
        $praticien = $this->praticienRepository->findById($id);

        if ($praticien === null) {
            return null;
        }
        
        $motifsVisite = $this->praticienRepository->getMotifsVisite($id);
        $moyensPaiement = $this->praticienRepository->getMoyensPaiement($id);
        
        return new PraticienDTO($praticien, $motifsVisite, $moyensPaiement);
    }

    public function getMotifsVisite(string $praticienId): array
    {
        return $this->praticienRepository->getMotifsVisite($praticienId);
    }

    public function getMoyensPaiement(string $praticienId): array
    {
        return $this->praticienRepository->getMoyensPaiement($praticienId);
    }

    public function rechercherPraticiensSpeVille(?string $specialiteLibelle, ?string $ville): array
    {
        $praticiens = $this->praticienRepository->findBySpecialiteAndVille($specialiteLibelle, $ville);
        $dtos = [];
        foreach ($praticiens as $praticien) {
            $dtos[] = new PraticienDTO($praticien);
        }
        return $dtos;
    }
}