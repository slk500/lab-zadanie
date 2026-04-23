<?php
declare(strict_types=1);


namespace App\Normalizers;

use App\Models\Patient;
use Illuminate\Database\Eloquent\Collection;

class PatientResultsNormalizer
{
    public function __construct(private readonly OrderNormalizer $orderNormalizer) {}

    public function normalize(Patient $patient, Collection $orders): array
    {
        return [
            'patient' => [
                'id'        => $patient->id,
                'name'      => $patient->name,
                'surname'   => $patient->surname,
                'sex'       => $patient->is_male ? 'm' : 'f',
                'birthDate' => $patient->birth_date,
            ],
            'orders' => $orders
                ->map(fn($order) => $this->orderNormalizer->normalize($order))
                ->values(),
        ];
    }
}
