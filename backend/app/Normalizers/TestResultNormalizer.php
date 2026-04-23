<?php
declare(strict_types=1);


namespace App\Normalizers;

use App\Models\TestResult;

class TestResultNormalizer
{
    public function normalize(TestResult $result): array
    {
        return [
            'name'      => $result->name,
            'value'     => $result->value,
            'reference' => $result->reference,
        ];
    }
}
