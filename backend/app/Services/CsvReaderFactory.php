<?php
declare(strict_types=1);


namespace App\Services;

use League\Csv\Exception as CsvException;
use League\Csv\Reader;

class CsvReaderFactory
{
    /**
     * @throws CsvException
     */
    public function createFromPath(string $path, string $delimiter = ';', int $headerOffset = 0): Reader
    {
        $reader = Reader::from($path);
        $reader->setDelimiter($delimiter);
        $reader->setHeaderOffset($headerOffset);

        return $reader;
    }
}
