<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Order;
use App\Models\Patient;
use App\Models\TestResult;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ImportCsvTest extends TestCase
{
    use RefreshDatabase;

    private string $csvPath;

    private const HEADER = 'patientId;patientName;patientSurname;patientSex;patientBirthDate;orderId;testName;testValue;testReference';

    protected function setUp(): void
    {
        parent::setUp();
        $this->csvPath = tempnam(sys_get_temp_dir(), 'csv_test_');
    }

    protected function tearDown(): void
    {
        if (file_exists($this->csvPath)) {
            unlink($this->csvPath);
        }
        parent::tearDown();
    }

    private function writeCsv(string $content): void
    {
        file_put_contents($this->csvPath, $content);
    }

    public function test_imports_valid_csv_successfully(): void
    {
        $this->writeCsv(implode("\n", [
            self::HEADER,
            '1;Piotr;Kowalski;male;1983-04-12;1;Glukoza;5.5;3.9-6.1',
            '2;Anna;Nowak;female;1990-01-01;2;Cholesterol;4.2;<5.0',
        ]));

        $this->artisan('import:csv', ['file' => $this->csvPath])
            ->assertSuccessful()
            ->expectsOutputToContain('Imported: 2, Errors: 0');

        $this->assertDatabaseCount('patients', 2);
        $this->assertDatabaseCount('orders', 2);
        $this->assertDatabaseCount('test_results', 2);
    }

    public function test_male_sex_stored_as_true(): void
    {
        $this->writeCsv(self::HEADER . "\n1;Piotr;Kowalski;male;1983-04-12;1;Glukoza;5.5;3.9-6.1");

        $this->artisan('import:csv', ['file' => $this->csvPath])->assertSuccessful();

        $this->assertDatabaseHas('patients', ['id' => 1, 'is_male' => true]);
    }

    public function test_female_sex_stored_as_false(): void
    {
        $this->writeCsv(self::HEADER . "\n2;Anna;Nowak;female;1990-01-01;1;Glukoza;5.5;3.9-6.1");

        $this->artisan('import:csv', ['file' => $this->csvPath])->assertSuccessful();

        $this->assertDatabaseHas('patients', ['id' => 2, 'is_male' => false]);
    }

    public function test_fails_when_file_not_found(): void
    {
        $this->artisan('import:csv', ['file' => '/nonexistent/path/file.csv'])
            ->assertFailed()
            ->expectsOutputToContain('Cannot open file');
    }

    public function test_fails_when_required_column_is_missing(): void
    {
        $this->writeCsv("patientId;patientName\n1;Piotr");

        $this->artisan('import:csv', ['file' => $this->csvPath])
            ->assertFailed()
            ->expectsOutputToContain('Missing columns');
    }

    public function test_skips_row_missing_patient_id(): void
    {
        $this->writeCsv(implode("\n", [
            self::HEADER,
            ';Piotr;Kowalski;male;1983-04-12;1;Glukoza;5.5;3.9-6.1',
            '2;Anna;Nowak;female;1990-01-01;2;Cholesterol;4.2;<5.0',
        ]));

        $this->artisan('import:csv', ['file' => $this->csvPath])
            ->assertSuccessful()
            ->expectsOutputToContain('Errors: 1');

        $this->assertDatabaseCount('test_results', 1);
    }

    public function test_skips_row_missing_order_id(): void
    {
        $this->writeCsv(implode("\n", [
            self::HEADER,
            '1;Piotr;Kowalski;male;1983-04-12;;Glukoza;5.5;3.9-6.1',
            '2;Anna;Nowak;female;1990-01-01;2;Cholesterol;4.2;<5.0',
        ]));

        $this->artisan('import:csv', ['file' => $this->csvPath])
            ->assertSuccessful()
            ->expectsOutputToContain('Errors: 1');

        $this->assertDatabaseCount('test_results', 1);
    }

    public function test_does_not_duplicate_patients_across_rows(): void
    {
        $this->writeCsv(implode("\n", [
            self::HEADER,
            '1;Piotr;Kowalski;male;1983-04-12;1;Glukoza;5.5;3.9-6.1',
            '1;Piotr;Kowalski;male;1983-04-12;1;Cholesterol;4.2;<5.0',
        ]));

        $this->artisan('import:csv', ['file' => $this->csvPath])->assertSuccessful();

        $this->assertDatabaseCount('patients', 1);
        $this->assertDatabaseCount('orders', 1);
        $this->assertDatabaseCount('test_results', 2);
    }

    public function test_does_not_duplicate_existing_patient_on_reimport(): void
    {
        $this->writeCsv(self::HEADER . "\n1;Piotr;Kowalski;male;1983-04-12;1;Glukoza;5.5;3.9-6.1");

        $this->artisan('import:csv', ['file' => $this->csvPath])->assertSuccessful();
        $this->artisan('import:csv', ['file' => $this->csvPath])->assertSuccessful();

        $this->assertDatabaseCount('patients', 1);
        $this->assertDatabaseCount('orders', 1);
        $this->assertDatabaseCount('test_results', 2);
    }

    public function test_fails_when_no_valid_records(): void
    {
        $this->writeCsv(implode("\n", [
            self::HEADER,
            ';Piotr;Kowalski;male;1983-04-12;;Glukoza;5.5;3.9-6.1',
        ]));

        $this->artisan('import:csv', ['file' => $this->csvPath])->assertFailed();

        $this->assertDatabaseCount('test_results', 0);
    }

    public function test_skips_row_with_non_numeric_patient_id(): void
    {
        $this->writeCsv(implode("\n", [
            self::HEADER,
            'abc;Piotr;Kowalski;male;1983-04-12;1;Glukoza;5.5;3.9-6.1',
            '2;Anna;Nowak;female;1990-01-01;2;Cholesterol;4.2;<5.0',
        ]));

        $this->artisan('import:csv', ['file' => $this->csvPath])
            ->assertSuccessful()
            ->expectsOutputToContain('Errors: 1');

        $this->assertDatabaseCount('test_results', 1);
    }

    public function test_skips_row_with_invalid_birth_date(): void
    {
        $this->writeCsv(implode("\n", [
            self::HEADER,
            '1;Piotr;Kowalski;male;12-04-1983;1;Glukoza;5.5;3.9-6.1',
            '2;Anna;Nowak;female;1990-01-01;2;Cholesterol;4.2;<5.0',
        ]));

        $this->artisan('import:csv', ['file' => $this->csvPath])
            ->assertSuccessful()
            ->expectsOutputToContain('Errors: 1');

        $this->assertDatabaseCount('test_results', 1);
    }

    public function test_skips_row_with_invalid_sex(): void
    {
        $this->writeCsv(implode("\n", [
            self::HEADER,
            '1;Piotr;Kowalski;other;1983-04-12;1;Glukoza;5.5;3.9-6.1',
            '2;Anna;Nowak;female;1990-01-01;2;Cholesterol;4.2;<5.0',
        ]));

        $this->artisan('import:csv', ['file' => $this->csvPath])
            ->assertSuccessful()
            ->expectsOutputToContain('Errors: 1');

        $this->assertDatabaseCount('test_results', 1);
    }

    public function test_skips_row_with_empty_required_field(): void
    {
        $this->writeCsv(implode("\n", [
            self::HEADER,
            '1;Piotr;Kowalski;male;1983-04-12;1;;5.5;3.9-6.1',
            '2;Anna;Nowak;female;1990-01-01;2;Cholesterol;4.2;<5.0',
        ]));

        $this->artisan('import:csv', ['file' => $this->csvPath])
            ->assertSuccessful()
            ->expectsOutputToContain('Errors: 1');

        $this->assertDatabaseCount('test_results', 1);
    }
}
