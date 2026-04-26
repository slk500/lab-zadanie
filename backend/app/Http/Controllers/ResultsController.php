<?php
declare(strict_types=1);


namespace App\Http\Controllers;

use App\Http\Resources\PatientResultsResource;
use App\Models\Patient;
use Illuminate\Http\JsonResponse;

class ResultsController
{
    public function list(): JsonResponse|PatientResultsResource
    {
        /** @var Patient $patient */
        $patient = auth()->user();

        $patient->load(['orders.testResults']);

        if ($patient->orders->isEmpty()) {
            return response()->json(['message' => 'No results found'], 404);
        }

        return new PatientResultsResource($patient);
    }
}
