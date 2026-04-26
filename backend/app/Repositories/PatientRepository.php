<?php
declare(strict_types=1);


namespace App\Repositories;

use App\Models\Patient;

class PatientRepository
{
    public function findByFullName(string $fullName): ?Patient
    {
        return Patient::whereRaw("CONCAT(name, surname) = ?", [$fullName])->first();
    }
}
