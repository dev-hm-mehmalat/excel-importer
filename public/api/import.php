<?php
require_once __DIR__ . '/../../vendor/autoload.php';

use Mehmalat\ExcelImporter\Service\ExcelImportService;
use Mehmalat\ExcelImporter\Repository\DatabaseRepository;

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Nur POST erlaubt']);
    exit;
}

if (!isset($_FILES['file'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Datei fehlt (Multipart-Upload mit Key "file")']);
    exit;
}

$tmp = $_FILES['file']['tmp_name'];
$name = basename($_FILES['file']['name']);
$allowed = ['xlsx', 'xls', 'csv'];
$ext = strtolower(pathinfo($name, PATHINFO_EXTENSION));
if (!in_array($ext, $allowed)) {
    http_response_code(400);
    echo json_encode(['error' => 'Nur Excel-Dateien erlaubt!']);
    exit;
}

$uploadDir = __DIR__ . '/../../uploads/';
$savePath = $uploadDir . uniqid('api_', true) . '.' . $ext;

if (!move_uploaded_file($tmp, $savePath)) {
    http_response_code(500);
    echo json_encode(['error' => 'Upload fehlgeschlagen']);
    exit;
}

try {
    $service = new ExcelImportService(new DatabaseRepository());
    $service->importFromFile($savePath);
    echo json_encode(['success' => true, 'message' => 'Import erfolgreich!']);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
