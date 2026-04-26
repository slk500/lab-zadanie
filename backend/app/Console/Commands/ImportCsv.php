<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Order;
use App\Models\Patient;
use App\Models\TestResult;
use App\Services\CsvReaderFactory;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Database\ConnectionInterface;
use League\Csv\Exception as CsvException;
use League\Csv\Reader;
use Psr\Log\LoggerInterface;

#[Signature('import:csv {file=results.csv : Path to the CSV file}')]
#[Description('Import patients and test results from a CSV file')]
class ImportCsv extends Command
{
    private const array REQUIRED_COLUMNS = [
        'patientId', 'patientName', 'patientSurname', 'patientSex',
        'patientBirthDate', 'orderId', 'testName', 'testValue', 'testReference',
    ];

    public function __construct(
        private readonly ConnectionInterface $db,
        private readonly LoggerInterface $logger,
        private readonly CsvReaderFactory $csvReaderFactory,
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        $filePath = $this->argument('file');

        try {
            $csv = $this->csvReaderFactory->createFromPath($filePath);
        } catch (CsvException $e) {
            $this->error("Cannot open file: {$e->getMessage()}");
            $this->logger->error("CSV import failed: {$e->getMessage()}");
            return self::FAILURE;
        }

        $missing = $this->getMissingColumns($csv);
        if ($missing) {
            $this->error('Missing columns: ' . implode(', ', $missing));
            $this->logger->error('CSV import failed: missing columns: ' . implode(', ', $missing));
            return self::FAILURE;
        }

        [$patients, $orders, $testResults, $errors] = $this->collectData($csv->getRecords());

        if (empty($testResults)) {
            $this->warn("No valid records to import. Errors: {$errors}.");
            $this->logger->warning("CSV import aborted: no valid records. Errors: {$errors}.");
            return self::FAILURE;
        }

        try {
            $this->db->transaction(function () use ($patients, $orders, $testResults) {
                Patient::insertOrIgnore(array_values($patients));
                Order::insertOrIgnore(array_values($orders));
                TestResult::insert($testResults);
            });
        } catch (\Throwable $e) {
            $this->error("Import failed: {$e->getMessage()}");
            $this->logger->error("CSV import failed: {$e->getMessage()}");
            return self::FAILURE;
        }

        $imported = count($testResults);
        $this->info("Import complete. Imported: {$imported}, Errors: {$errors}.");
        $this->logger->info("CSV import finished. Imported: {$imported}, Errors: {$errors}.");

        return self::SUCCESS;
    }

    private function collectData(iterable $records): array
    {
        $patients    = [];
        $orders      = [];
        $testResults = [];
        $errors      = 0;
        $rowNumber   = 1; // because first line is header

        foreach ($records as $record) {
            $rowNumber++;

            $row = array_map('trim', $record);

            $validationErrors = $this->validateRow($row);
            if (!empty($validationErrors)) {
                $msg = "Row {$rowNumber}: " . implode(', ', $validationErrors) . ', skipping.';
                $this->warn($msg);
                $this->logger->warning($msg);
                $errors++;
                continue;
            }

            $patients[$row['patientId']] = $this->mapPatient($row);
            $orders[$row['orderId']]     = $this->mapOrder($row);
            $testResults[]               = $this->mapTestResult($row);
        }

        return [$patients, $orders, $testResults, $errors];
    }

    private function mapPatient(array $row): array
    {
        return [
            'id'         => (int) $row['patientId'],
            'name'       => $row['patientName'],
            'surname'    => $row['patientSurname'],
            'is_male'    => $row['patientSex'] === 'male',
            'birth_date' => $row['patientBirthDate'],
        ];
    }

    private function mapOrder(array $row): array
    {
        return [
            'id'         => (int) $row['orderId'],
            'patient_id' => (int) $row['patientId'],
        ];
    }

    private function mapTestResult(array $row): array
    {
        return [
            'order_id'  => (int) $row['orderId'],
            'name'      => $row['testName'],
            'value'     => $row['testValue'],
            'reference' => $row['testReference'],
        ];
    }

    private function validateRow(array $row): array
    {
        $errors = [];

        foreach (self::REQUIRED_COLUMNS as $col) {
            if (empty($row[$col])) {
                $errors[] = "missing {$col}";
            }
        }

        if (!empty($row['patientId']) && (!ctype_digit($row['patientId']) || (int) $row['patientId'] <= 0)) {
            $errors[] = "patientId must be a positive integer";
        }

        if (!empty($row['orderId']) && (!ctype_digit($row['orderId']) || (int) $row['orderId'] <= 0)) {
            $errors[] = "orderId must be a positive integer";
        }

        if (!empty($row['patientBirthDate'])) {
            $date = \DateTimeImmutable::createFromFormat('Y-m-d', $row['patientBirthDate']);
            if (!$date || $date->format('Y-m-d') !== $row['patientBirthDate']) {
                $errors[] = "patientBirthDate must be YYYY-MM-DD";
            }
        }

        if (!empty($row['patientSex']) && !in_array($row['patientSex'], ['male', 'female'], true)) {
            $errors[] = "patientSex must be 'male' or 'female'";
        }

        return $errors;
    }

    private function getMissingColumns(Reader $csv): array
    {
        return array_diff(self::REQUIRED_COLUMNS, $csv->getHeader());
    }
}
