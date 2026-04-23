<?php
declare(strict_types=1);


namespace App\Services;

use App\Models\Patient;
use App\Repositories\PatientRepository;

class AuthService
{
    public function __construct(private readonly PatientRepository $patients) {}

    public function attempt(string $login, string $password): ?Patient
    {
        $patient = $this->patients->findByFullName($login);

        return $patient?->birth_date === $password ? $patient : null;
    }
}
