<?php
declare(strict_types=1);


namespace App\Normalizers;

use App\Models\Order;

class OrderNormalizer
{
    public function __construct(private readonly TestResultNormalizer $testResultNormalizer) {}

    public function normalize(Order $order): array
    {
        return [
            'orderId' => (string) $order->id,
            'results' => $order->testResults
                ->map(fn($result) => $this->testResultNormalizer->normalize($result))
                ->values(),
        ];
    }
}
