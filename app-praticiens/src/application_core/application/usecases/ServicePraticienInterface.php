<?php

namespace toubilib\core\application\usecases;

use toubilib\core\application\dto\PraticienDTO;

interface ServicePraticienInterface
{
    public function listerPraticiens(): array;
    public function RecherchePraticienByID(string $id): ?PraticienDTO;
    public function rechercherPraticiensSpeVille(?string $specialiteLibelle, ?string $ville): array; // par spécialité et/ou ville
    public function getMotifsVisite(string $praticienId): array;
    public function getMoyensPaiement(string $praticienId): array;
}