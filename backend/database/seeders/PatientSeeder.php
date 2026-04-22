<?php
declare(strict_types=1);


namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Artisan;

class PatientSeeder extends Seeder
{
    public function run(): void
    {
        $csvPath = base_path('results.csv');

        if (!file_exists($csvPath)) {
            $this->command->warn("results.csv not found at {$csvPath}, skipping import.");
            return;
        }

        Artisan::call('import:csv', ['file' => $csvPath], $this->command->getOutput());
    }
}
