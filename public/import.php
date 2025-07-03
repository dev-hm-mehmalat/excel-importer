<?php
// Autoload alle Abhängigkeiten und Klassen
require_once __DIR__ . '/../vendor/autoload.php';

// Service und Repository importieren (PSR-4 Autoloading)
use Mehmalat\ExcelImporter\Service\ExcelImportService;
use Mehmalat\ExcelImporter\Repository\DatabaseRepository;

// Excel-Dateipfad (Anpassen, wenn du einen anderen Dateinamen/Pfad hast)
$excelFile = __DIR__ . '/../uploads/example.xlsx';

try {
    // Service-Objekt erzeugen und Repository übergeben (Dependency Injection)
    $service = new ExcelImportService(new DatabaseRepository());
    
    // Starte den Importvorgang
    $service->importFromFile($excelFile);

    // Ausgabe bei Erfolg
    echo "✅ Import abgeschlossen! Überprüfe die Datei import.log\n";
} catch (Exception $e) {
    // Fehlerausgabe, falls Import fehlschlägt
    echo "❌ Fehler beim Import: " . $e->getMessage() . "\n";
}

