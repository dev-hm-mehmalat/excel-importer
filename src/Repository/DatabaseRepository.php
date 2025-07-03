<?php
namespace Mehmalat\ExcelImporter\Repository;

class DatabaseRepository
{
    public function save(array $data)
    {
        // Loggt jede importierte Zeile in eine Datei
        $logfile = __DIR__ . '/../../import.log';
        file_put_contents($logfile, implode(';', $data) . PHP_EOL, FILE_APPEND);
    }
}
