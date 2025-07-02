<?php
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../src/config.php';

use PhpOffice\PhpSpreadsheet\IOFactory;
use Mehmalat\ExcelImporter\ExcelImporter;

$excelFile = __DIR__ . '/../uploads/example.xlsx';

try {
    $spreadsheet = IOFactory::load($excelFile);
    $sheet = $spreadsheet->getActiveSheet();

    $importer = new ExcelImporter();

    foreach ($sheet->getRowIterator() as $row) {
        $data = [];
        foreach ($row->getCellIterator() as $cell) {
            $data[] = $cell->getFormattedValue();
        }

        // Zeile an die Importer-Klasse übergeben
        $importer->import($data);
    }
} catch (Exception $e) {
    echo "❌ Fehler beim Einlesen der Excel-Datei: " . $e->getMessage();
}
