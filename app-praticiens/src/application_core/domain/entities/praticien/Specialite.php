<?php

namespace toubilib\core\domain\entities\praticien;

class Specialite
{
    public int $id;
    public string $libelle;
    public ?string $description;

    public function __construct(int $id, string $libelle, ?string $description = null)
    {
        $this->id = $id;
        $this->libelle = $libelle;
        $this->description = $description;
    }
}
