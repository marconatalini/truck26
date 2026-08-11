<?php

namespace App\Service;

use App\Entity\Address;
use App\Entity\Place;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class ExcelImportService
{
    private const EXPECTED_PLACE_HEADERS = [
        0 => 'nominativo',
        1 => 'indirizzo',
        2 => 'comune',
        3 => 'provincia',
        4 => 'coordinate'
    ];

    private $worksheet;

    public function validateExcelPlaceFile(UploadedFile $file): array
    {
        // Carichiamo il file con PhpSpreadsheet
        $spreadsheet = IOFactory::load($file->getPathname());
        $this->worksheet = $spreadsheet->getActiveSheet();

        // Lettura delle colonne della riga 1
        $actualHeaders = [];
        for ($col = 1; $col <= count(self::EXPECTED_PLACE_HEADERS); $col++) {
            $cellValue = $this->worksheet->getCell([$col, 1])->getValue();
            $actualHeaders[] = mb_strtolower(trim((string) $cellValue));
        }

        // Validazione delle intestazioni
        $errors = [];
        foreach (self::EXPECTED_PLACE_HEADERS as $index => $expectedHeader) {
            if (($actualHeaders[$index] ?? '') !== $expectedHeader) {
                $columnLetter = chr(65 + $index);

                // Aggiungiamo l'errore direttamente al campo del Form
                 $errors[] = new FormError(sprintf(
                    'Colonna %s non valida: attesa "%s", trovata "%s".',
                    $columnLetter,
                    $expectedHeader,
                    $actualHeaders[$index] !== '' ? $actualHeaders[$index] : 'vuota'
                ));
            }
        }

        return $errors;
    }

    public function getPlaces(): array
    {
        $places = [];
        $rowIdx = 2;
        // Carico i dati
        while (true) {
            $name = $this->worksheet->getCell([1, $rowIdx])->getValue();
            if (!$name) {break;}

            $place = new Place();
            $place->setName($name);
            $address = new Address();
            $address->setAddressLine1($this->worksheet->getCell([2, $rowIdx])->getValue());
            $address->setCity($this->worksheet->getCell([3, $rowIdx])->getValue());
            $address->setProvince($this->worksheet->getCell([4, $rowIdx])->getValue());
            $address->setCoordinates($this->worksheet->getCell([5, $rowIdx])->getValue());
            $place->setAddress($address);
            $places[] = $place;
            $rowIdx ++;
        }

        return $places;
    }
}
