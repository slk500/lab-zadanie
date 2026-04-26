<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Patient;

class AuthService
{
    public function attempt(string $login, string $password): ?Patient
    {
        $patient = Patient::findByFullName($login);

        return $patient?->birth_date === $password ? $patient : null;
    }
}
