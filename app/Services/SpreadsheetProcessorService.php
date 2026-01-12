<?php

namespace App\Services;

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use Illuminate\Support\Facades\Log;

class SpreadsheetProcessorService
{
    // Column name constants
    private const WEEKLY_SUMMARY = 'weekly_summary';
    private const ADD_NOTES = 'add_notes';
    private const RISK_INDICATOR = 'risk_indicator';
    private const SOURCE_LINK = 'source_link';
    private const LINK2 = 'link2';
    private const LINK3 = 'link3';

    private const NEW_COLUMNS = [
        'business_report',
        'affected_industry',
        'impact_level',
        'impact_rationale',
        'associated_risks',
        'business_advisory',
        'similar_news_link'
    ];

    private GroqAIService $groqService;
    private array $data;
    private array $originalHeaders;
    private array $lowerHeaders;
    private int $insertPosition;

    public function __construct(GroqAIService $groqService)
    {
        $this->groqService = $groqService;
    }

    /**
     * Process the uploaded file and return processed spreadsheet
     */
    public function processFile(string $filePath): Spreadsheet
    {
        try {
            $this->loadSpreadsheet($filePath);
            $this->prepareHeaders();
            $this->processRows();

            return $this->createOutputSpreadsheet();
        } catch (\Exception $e) {
            Log::error('Spreadsheet processing failed', [
                'error' => $e->getMessage(),
                'file' => $filePath
            ]);
            throw $e;
        }
    }

    /**
     * Load spreadsheet into data array
     */
    private function loadSpreadsheet(string $filePath): void
    {
        $spreadsheet = IOFactory::load($filePath);
        $worksheet = $spreadsheet->getActiveSheet();
        $this->data = $worksheet->toArray();

        if (empty($this->data)) {
            throw new \RuntimeException('The uploaded file is empty');
        }

        $this->originalHeaders = $this->data[0];
        $this->lowerHeaders = array_map('strtolower', $this->originalHeaders);
    }

    /**
     * Prepare headers by inserting new columns
     */
    private function prepareHeaders(): void
    {
        $link3Index = $this->findColumnIndex(self::LINK3);
        $this->insertPosition = ($link3Index !== false) ? $link3Index + 1 : count($this->originalHeaders);

        array_splice($this->originalHeaders, $this->insertPosition, 0, self::NEW_COLUMNS);
        $this->data[0] = $this->originalHeaders;

        // Update lower headers after insertion
        $this->lowerHeaders = array_map('strtolower', $this->originalHeaders);
    }

    /**
     * Process all data rows
     */
    private function processRows(): void
    {
        $totalRows = count($this->data);

        for ($i = 1; $i < $totalRows; $i++) {
            try {
                $this->data[$i] = $this->processRow($this->data[$i], $i);
            } catch (\Exception $e) {
                Log::warning("Failed to process row {$i}", [
                    'error' => $e->getMessage()
                ]);
                // Insert empty data for failed rows
                $emptyData = array_fill(0, count(self::NEW_COLUMNS), '');
                array_splice($this->data[$i], $this->insertPosition, 0, $emptyData);
            }
        }
    }

    /**
     * Process a single row
     */
    private function processRow(array $row, int $rowNumber): array
    {
        $rowData = $this->extractRowData($row);

        if (empty($rowData['riskIndicator'])) {
            // No risk indicator, insert empty columns
            $newData = array_fill(0, count(self::NEW_COLUMNS), '');
        } else {
            Log::info("Processing row {$rowNumber}");
            $newData = $this->generateAIData($rowData);
        }

        array_splice($row, $this->insertPosition, 0, $newData);
        return $row;
    }

    /**
     * Extract relevant data from row
     */
    private function extractRowData(array $row): array
    {
        return [
            'addNotes' => $this->getCellValue($row, self::WEEKLY_SUMMARY)
                ?: $this->getCellValue($row, self::ADD_NOTES),
            'riskIndicator' => $this->getCellValue($row, self::RISK_INDICATOR),
            'sourceLink' => $this->getCellValue($row, self::SOURCE_LINK),
            'link2' => $this->getCellValue($row, self::LINK2),
            'link3' => $this->getCellValue($row, self::LINK3),
        ];
    }

    /**
     * Generate AI data for the row
     */
    private function generateAIData(array $rowData): array
    {
        try {
            $aiReport = $this->groqService->generateBusinessReport(
                $rowData['riskIndicator'],
                $rowData['addNotes'],
                $rowData['sourceLink']
            );

            // Use related_link from AI report, or fallback to existing links
            $similarNewsLink = $aiReport['related_link']
                ?? $rowData['link2']
                ?? $rowData['link3']
                ?? '';

            return [
                $aiReport['business_report'] ?? '',
                $aiReport['affected_industry'] ?? '',
                $aiReport['impact_level'] ?? '',
                $aiReport['impact_rationale'] ?? '',
                $aiReport['associated_risks'] ?? '',
                $aiReport['business_advisory'] ?? '',
                $similarNewsLink
            ];
        } catch (\Exception $e) {
            Log::error('AI generation failed', ['error' => $e->getMessage()]);
            return [
                'AI Processing Failed',
                'Unknown',
                'Unknown',
                'Processing error occurred',
                'Unable to assess risks',
                'Please review manually',
                ''
            ];
        }
    }

    /**
     * Determine similar news link
     */
    private function determineSimilarNewsLink(array $rowData): string
    {
        // Fallback to existing links if no AI-generated link
        return $rowData['link2'] ?: ($rowData['link3'] ?: '');
    }

    /**
     * Create output spreadsheet
     */
    private function createOutputSpreadsheet(): Spreadsheet
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->fromArray($this->data, null, 'A1');

        return $spreadsheet;
    }

    /**
     * Find column index by name (case-insensitive)
     */
    private function findColumnIndex(string $columnName): int|false
    {
        return array_search(strtolower($columnName), $this->lowerHeaders);
    }

    /**
     * Get cell value safely
     */
    private function getCellValue(array $row, string $columnName): string
    {
        $index = $this->findColumnIndex($columnName);
        return ($index !== false && isset($row[$index])) ? trim($row[$index]) : '';
    }
}
