<?php
declare(strict_types=1);


namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'orderId' => (string) $this->id,
            'results' => TestResultResource::collection($this->testResults),
        ];
    }
}
