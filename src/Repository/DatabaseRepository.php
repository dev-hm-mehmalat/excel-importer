<?php
namespace Mehmalat\ExcelImporter\Repository;

class DatabaseRepository
{
    public function save(array $data)
    {
        // Hier wird später wirklich in die DB gespeichert
        echo "Würde speichern: " . json_encode($data) . "\n";
    }
}


