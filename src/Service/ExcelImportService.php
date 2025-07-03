<?php
namespace Mehmalat\ExcelImporter\Service;

use PhpOffice\PhpSpreadsheet\IOFactory;
use Mehmalat\ExcelImporter\Repository\DatabaseRepository;

class ExcelImportService
{
    private $repository;

    public function __construct(DatabaseRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Importiert Daten aus einer Excel-Datei und prüft Pflichtfelder.
     *
     * @param string $filePath
     * @throws \Exception bei fehlenden Pflichtfeldern
     */
    public function importFromFile($filePath)
    {
        $spreadsheet = IOFactory::load($filePath);
        $sheet = $spreadsheet->getActiveSheet();

        foreach ($sheet->getRowIterator() as $i => $row) {
            $data = [];
            foreach ($row->getCellIterator() as $cell) {
                $data[] = $cell->getFormattedValue();
            }
            // Pflichtfeldprüfung: Spalte 0 und 1 müssen gesetzt sein!
            if (empty($data[0]) || empty($data[1])) {
                throw new \Exception("Fehler: Pflichtfelder in Zeile " . ($i + 1) . " fehlen!");
                // Alternativ: Nur fehlerhafte Zeilen überspringen:
                // continue;
            }
            $this->repository->save($data);
        }
    }
}
