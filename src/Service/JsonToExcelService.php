<?php

namespace App\Service;

use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\StreamedResponse;

class JsonToExcelService
{
    /**
     * Appiattisce un array/oggetto JSON mantenendo la gerarchia con la notazione a punto.
     */
    public function flattenArray(array $array, string $prefix = ''): array
    {
        $result = [];

        foreach ($array as $key => $value) {
            $newKey = $prefix === '' ? (string)$key : $prefix . '.' . $key;

            if (is_array($value)) {
                $result = array_merge($result, $this->flattenArray($value, $newKey));
            } else {
                // Gestione dei valori null o scalari
                $result[$newKey] = $value;
            }
        }

        return $result;
    }

    /**
     * Genera la StreamedResponse per scaricare l'Excel a partire da una stringa JSON o array.
     */
    public function createStreamedResponseFromJson(string|array $jsonInput, string $filename = 'export.xlsx'): StreamedResponse
    {
        // Se passiamo una stringa JSON, la decodifichiamo in array associativo
        $data = is_string($jsonInput) ? json_decode($jsonInput, true) : $jsonInput;

        if (!is_array($data)) {
            throw new \InvalidArgumentException('JSON non valido o formato non supportato.');
        }

        return new StreamedResponse(
            function () use ($data) {
                if (empty($data)) {
                    return;
                }

                // 1. Appiattimento di tutti i record e pulizia intestazioni
                $flattenedRows = [];
                $headers = [];

                foreach ($data as $record) {
                    $flatRecord = $this->flattenArray($record);
                    $flattenedRows[] = $flatRecord;

                    foreach (array_keys($flatRecord) as $header) {
                        if (!in_array($header, $headers, true)) {
                            $headers[] = $header;
                        }
                    }
                }

                // 2. Inizializzazione Spreadsheet
                $spreadsheet = new Spreadsheet();
                $sheet = $spreadsheet->getActiveSheet();

                // 3. Scrittura delle Intestazioni (Riga 1)
                $colIndex = 1;
                foreach ($headers as $header) {
                    $sheet->setCellValue([$colIndex, 1], $header);
                    $colIndex++;
                }

                // 4. Scrittura dei Dati (Riga 2+)
                $rowIndex = 2;
                foreach ($flattenedRows as $row) {
                    $colIndex = 1;
                    foreach ($headers as $header) {
                        $val = $row[$header] ?? '';
                        $sheet->setCellValue([$colIndex, $rowIndex], $val);
                        $colIndex++;
                    }
                    $rowIndex++;
                }

                // 4b. Auto-ridimensionamento delle colonne
                foreach (range(1, count($headers)) as $col) {
                    $columnLetter = Coordinate::stringFromColumnIndex($col);
                    $sheet->getColumnDimension($columnLetter)->setAutoSize(true);
                }

                // 5. Streaming diretto all'output
                $writer = new Xlsx($spreadsheet);
                $writer->save('php://output');
            },
            200,
            [
                'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                'Content-Disposition' => sprintf('attachment; filename="%s"', $filename),
                'Cache-Control' => 'max-age=0',
            ]
        );
    }
}
