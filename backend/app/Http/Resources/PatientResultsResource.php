<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PatientResultsResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'patient' => [
                'id'        => $this->id,
                'name'      => $this->name,
                'surname'   => $this->surname,
                'sex'       => $this->is_male ? 'm' : 'f',
                'birthDate' => $this->birth_date,
            ],
            'orders' => OrderResource::collection($this->orders),
        ];
    }
}
