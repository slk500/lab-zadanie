<?php
declare(strict_types=1);


namespace App\Http\Controllers;

use App\Models\Patient;
use App\Repositories\PatientRepository;
use App\Normalizers\PatientResultsNormalizer;
use Illuminate\Http\JsonResponse;

class ResultsController
{
    public function __construct(
        private readonly PatientRepository $patients,
        private readonly PatientResultsNormalizer $normalizer,
    ) {}

    public function index(): JsonResponse
    {
        /** @var Patient $patient */
        $patient = auth()->user();

        $orders = $this->patients->getOrdersWithResults($patient);

        if ($orders->isEmpty()) {
            return response()->json(['message' => 'No results found'], 404);
        }

        return response()->json($this->normalizer->normalize($patient, $orders));
    }
}
