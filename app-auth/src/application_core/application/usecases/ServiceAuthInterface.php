<?php
namespace toubilib\core\application\usecases;

use toubilib\core\application\dto\AuthDTO;
use toubilib\core\application\dto\InputRegisterPatientDTO;

interface ServiceAuthInterface
{
    public function authentifier(string $email , string $password): ?AuthDTO;
    public function inscrirePatient(InputRegisterPatientDTO $dto): AuthDTO;
}