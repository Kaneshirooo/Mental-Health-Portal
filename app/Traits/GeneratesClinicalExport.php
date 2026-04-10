<?php

namespace App\Traits;

use Illuminate\Support\LazyCollection;
use Symfony\Component\HttpFoundation\StreamedResponse;

trait GeneratesClinicalExport
{
    /**
     * Generate a memory-efficient CSV export using PHP Generators (yield).
     *
     * @param mixed $query Eloquent Builder, Query Builder, or LazyCollection
     * @param array $headers List of CSV column headers
     * @param callable $rowCallback Function that maps a model to an array of row values
     * @param string $filename Name of the downloaded file
     * @return StreamedResponse
     */
    public function streamCsvExport(mixed $query, array $headers, callable $rowCallback, string $filename = 'export.csv'): StreamedResponse
    {
        $headersLine = implode(',', array_map(fn($h) => '"' . str_replace('"', '""', $h) . '"', $headers)) . "\n";

        $response = new StreamedResponse(function () use ($query, $headersLine, $rowCallback) {
            $handle = fopen('php://output', 'w');
            fwrite($handle, $headersLine);

            $generator = $this->generateCsvRows($query, $rowCallback);
            
            foreach ($generator as $row) {
                fputcsv($handle, $row);
            }

            fclose($handle);
        });

        $response->headers->set('Content-Type', 'text/csv');
        $response->headers->set('Content-Disposition', 'attachment; filename="' . $filename . '"');
        $response->headers->set('Cache-Control', 'no-cache, no-store, must-revalidate');
        $response->headers->set('Pragma', 'no-cache');
        $response->headers->set('Expires', '0');

        return $response;
    }

    /**
     * Yields mapped rows efficiently.
     */
    private function generateCsvRows($query, callable $rowCallback)
    {
        // If it's an Eloquent Builder, use chunking/cursor to keep memory low
        if (method_exists($query, 'cursor')) {
            foreach ($query->cursor() as $record) {
                yield $rowCallback($record);
            }
        } elseif ($query instanceof LazyCollection) {
            foreach ($query as $record) {
                yield $rowCallback($record);
            }
        } else {
            // Fallback for standard collections or arrays
            foreach ($query as $record) {
                yield $rowCallback($record);
            }
        }
    }
}
