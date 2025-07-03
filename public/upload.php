<?php
session_start();
require_once __DIR__ . '/../vendor/autoload.php';

use Mehmalat\ExcelImporter\Service\ExcelImportService;
use Mehmalat\ExcelImporter\Repository\DatabaseRepository;

$msg = null;
$excelPreview = [];

// --- 1. Datei wurde hochgeladen ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['excel'])) {
    $uploadDir = __DIR__ . '/../uploads/';
    $fileName = uniqid('excel_', true) . '.' . strtolower(pathinfo($_FILES['excel']['name'], PATHINFO_EXTENSION));
    $filePath = $uploadDir . $fileName;

    // ---- Dateiendung und MIME-Type prüfen ----
    $allowedExtensions = ['xlsx', 'xls', 'csv'];
    $allowedMime = [
        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        'application/vnd.ms-excel',
        'text/csv',
        'application/csv',
        'text/plain',
    ];

    $ext = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
    $mime = mime_content_type($_FILES['excel']['tmp_name']);

    if (!in_array($ext, $allowedExtensions)) {
        $msg = "❌ Nur Excel-Dateien (.xlsx, .xls, .csv) sind erlaubt!";
    } elseif (!in_array($mime, $allowedMime)) {
        $msg = "❌ Ungültiger Dateityp! Hochgeladene Datei ist kein echtes Excel-/CSV-Dokument.<br>MIME-Typ: $mime";
    } elseif (!move_uploaded_file($_FILES['excel']['tmp_name'], $filePath)) {
        $msg = "❌ Datei-Upload fehlgeschlagen!";
    } else {
        // Vorschau erzeugen
        try {
            $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($filePath);
            $sheet = $spreadsheet->getActiveSheet();
            $maxRows = 5;
            foreach ($sheet->getRowIterator() as $i => $row) {
                if ($i >= $maxRows) break;
                $cells = [];
                foreach ($row->getCellIterator() as $cell) {
                    $cells[] = $cell->getFormattedValue();
                }
                $excelPreview[] = $cells;
            }
            $_SESSION['excel_file'] = $filePath; // Merke für späteren Import
            $msg = "✅ Datei hochgeladen! Prüfe die Vorschau und klicke auf 'Jetzt importieren'.";
        } catch (Exception $e) {
            $msg = "❌ Fehler beim Einlesen der Datei: " . $e->getMessage();
        }
    }
}

// --- 2. „Jetzt importieren“ wurde geklickt ---
if (isset($_POST['do_import']) && isset($_SESSION['excel_file'])) {
    try {
        $service = new ExcelImportService(new DatabaseRepository());
        $service->importFromFile($_SESSION['excel_file']);
        $msg = "✅ Daten erfolgreich importiert!";
        unset($_SESSION['excel_file']);
        $excelPreview = [];
    } catch (Exception $e) {
        $msg = "❌ Fehler beim Import: " . $e->getMessage();
    }
}


?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <title>Excel-Import hochladen</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- Bootstrap 5 CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
  <div class="container py-5">
    <div class="card shadow-sm p-4">
      <h1 class="mb-4">Excel-Import hochladen</h1>
      <?php if ($msg): ?>
        <div class="alert <?= strpos($msg, '✅') !== false ? 'alert-success' : 'alert-danger' ?>">
          <?= $msg ?>
        </div>
      <?php endif; ?>

      <?php if (!$excelPreview && !isset($_SESSION['excel_file'])): ?>
        <form action="" method="post" enctype="multipart/form-data" class="mb-4">
          <input type="file" name="excel" class="form-control mb-3" required>
          <button type="submit" class="btn btn-primary">
            <i class="bi bi-upload"></i> Hochladen
          </button>
        </form>
      <?php endif; ?>

      <?php if ($excelPreview && isset($_SESSION['excel_file'])): ?>
        <h2 class="mb-3">Vorschau (erste 5 Zeilen)</h2>
        <div class="table-responsive mb-3">
          <table class="table table-bordered table-striped">
            <tbody>
              <?php foreach ($excelPreview as $row): ?>
                <tr>
                  <?php foreach ($row as $cell): ?>
                    <td><?= htmlspecialchars($cell) ?></td>
                  <?php endforeach; ?>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
        <form method="post">
          <button type="submit" name="do_import" class="btn btn-success">Jetzt importieren</button>
        </form>
      <?php endif; ?>
    </div>
  </div>
  <!-- Optional: Bootstrap Icons -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
</body>
</html>
