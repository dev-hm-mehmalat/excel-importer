<?php
// Immer ganz oben:
require_once __DIR__ . '/../vendor/autoload.php';

use Mehmalat\ExcelImporter\Service\ExcelImportService;
use Mehmalat\ExcelImporter\Repository\DatabaseRepository;

// Restlicher PHP-Code kommt jetzt!

session_start(); // Falls du Vorschau oder Session brauchst

$msg = null;
$excelPreview = [];

// Excel-Datei wurde abgeschickt
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['excel'])) {
    $uploadDir = __DIR__ . '/../uploads/';
    $fileName = basename($_FILES['excel']['name']);
    $filePath = $uploadDir . $fileName;

    // Datei speichern
    if (move_uploaded_file($_FILES['excel']['tmp_name'], $filePath)) {
        try {
            // Vorschau erzeugen (optional)
            $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($filePath);
            $sheet = $spreadsheet->getActiveSheet();
            $maxRows = 5;
            $i = 0;
            foreach ($sheet->getRowIterator() as $row) {
                if ($i++ >= $maxRows) break;
                $cells = [];
                foreach ($row->getCellIterator() as $cell) {
                    $cells[] = $cell->getFormattedValue();
                }
                $excelPreview[] = $cells;
            }

            // Jetzt wirklich importieren
            $service = new ExcelImportService(new DatabaseRepository());
            $service->importFromFile($filePath);

            $msg = "✅ Datei erfolgreich importiert!";
        } catch (Exception $e) {
            $msg = "❌ Fehler beim Import: " . $e->getMessage();
        }
    } else {
        $msg = "❌ Datei-Upload fehlgeschlagen!";
    }
}
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <title>Excel-Import hochladen</title>
</head>
<body>
    <h1>Excel-Import hochladen</h1>
    <?php if ($msg) echo "<p>$msg</p>"; ?>
    <form action="" method="post" enctype="multipart/form-data">
        <input type="file" name="excel" required>
        <button type="submit">Importieren</button>
    </form>
    <?php if (!empty($excelPreview)) : ?>
        <h2>Vorschau (erste 5 Zeilen)</h2>
        <table border="1">
            <?php foreach ($excelPreview as $row) : ?>
                <tr>
                    <?php foreach ($row as $cell) : ?>
                        <td><?= htmlspecialchars($cell) ?></td>
                    <?php endforeach; ?>
                </tr>
            <?php endforeach; ?>
        </table>
    <?php endif; ?>
</body>
</html>
