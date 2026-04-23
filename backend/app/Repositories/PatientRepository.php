<?php
declare(strict_types=1);


namespace App\Repositories;

use App\Models\Order;
use App\Models\Patient;
use Illuminate\Database\Eloquent\Collection;

class PatientRepository
{
    public function findByFullName(string $fullName): ?Patient
    {
        return Patient::whereRaw("CONCAT(name, surname) = ?", [$fullName])->first();
    }

    public function getOrdersWithResults(Patient $patient): Collection
    {
        return $patient->orders()->with('testResults')->get();
    }
}
