<?php
namespace Mehmalat\ExcelImporter\Repository;

class DatabaseRepository
{
    // Definiere die Pflichtfelder
    private $requiredFields = ['Name', 'E-Mail'];
    private $headerChecked = false;
    private $columnMap = [];

    public function save(array $row)
    {
        // Prüfe beim ersten Aufruf, ob die Pflichtfelder als Spalten existieren
        if (!$this->headerChecked) {
            foreach ($this->requiredFields as $field) {
                if (!in_array($field, $row)) {
                    throw new \Exception("Pflichtfeld '$field' fehlt in der Excel-Datei!");
                }
            }
            // Merke die Spalten-Positionen für die Pflichtfelder
            $this->columnMap = array_flip($row);
            $this->headerChecked = true;
            return; // Header nicht als Datensatz loggen
        }

        // Jede Zeile prüfen: Sind alle Pflichtfelder ausgefüllt?
        foreach ($this->requiredFields as $field) {
            $col = $this->columnMap[$field] ?? null;
            if ($col === null || empty($row[$col])) {
                throw new \Exception("Pflichtfeld '$field' ist in einer Zeile leer!");
            }
        }

        // Logge die Zeile wie bisher
        $logfile = __DIR__ . '/../../logs/import.log';
        file_put_contents($logfile, implode(';', $row) . PHP_EOL, FILE_APPEND);
    }
}
