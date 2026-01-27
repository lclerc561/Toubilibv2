<?php
namespace toubilib\core\application\usecases;

use toubilib\core\application\dto\AuthDTO;
use toubilib\core\application\dto\InputRegisterPatientDTO;
use toubilib\core\application\ports\AuthRepositoryInterface;
use toubilib\core\application\ports\PatientRepositoryInterface;
use toubilib\core\domain\entities\Auth;
use toubilib\core\domain\entities\Patient;
use Ramsey\Uuid\Uuid;
use Exception;

class ServiceAuth implements ServiceAuthInterface
{
    private AuthRepositoryInterface $authRepository;
    private PatientRepositoryInterface $patientRepository;

    public function __construct(
        AuthRepositoryInterface $authRepository,
        PatientRepositoryInterface $patientRepository
    ) {
        $this->authRepository = $authRepository;
        $this->patientRepository = $patientRepository;
    }

    public function authentifier(string $email , string $password): ?AuthDTO
    {
        $auth= $this->authRepository->findByEmail($email);
        if(!$auth || !password_verify($password, $auth->getMdp())){
            return null;
        }
        return new AuthDTO($auth);
    }

    public function inscrirePatient(InputRegisterPatientDTO $dto): AuthDTO
    {
        // Vérifier que l'email n'existe pas déjà
        $existingAuth = $this->authRepository->findByEmail($dto->email);
        if ($existingAuth !== null) {
            throw new Exception("Cet email est déjà utilisé");
        }

        // Générer un UUID unique pour l'ID (même ID pour users et patient)
        $id = Uuid::uuid4()->toString();

        // Hasher le mot de passe
        $hashedPassword = password_hash($dto->mdp, PASSWORD_DEFAULT);

        // Créer l'entité Auth (role = 1 pour patient)
        $auth = new Auth(
            $id,
            $dto->email,
            $hashedPassword,
            1 // role patient
        );

        // Créer l'entité Patient
        $dateNaissance = $dto->dateNaissance ? new \DateTime($dto->dateNaissance) : null;
        $patient = new Patient(
            $id,
            $dto->nom,
            $dto->prenom,
            $dateNaissance,
            $dto->adresse,
            $dto->codePostal,
            $dto->ville,
            $dto->email,
            $dto->telephone
        );

        // Sauvegarder dans les deux bases
        // Note: On ne peut pas faire de transaction cross-database, donc on sauvegarde dans l'ordre
        // Si la deuxième insertion échoue, on aura un état incohérent, mais c'est acceptable pour cette implémentation
        try {
            $this->authRepository->save($auth);
            $this->patientRepository->save($patient);
        } catch (\Exception $e) {
            throw new Exception("Erreur lors de l'inscription: " . $e->getMessage());
        }

        return new AuthDTO($auth);
    }
}