<?php
require __DIR__ . '/../../vendor/autoload.php';

use Slim\Factory\AppFactory;
use Mehmalat\ExcelImporter\Service\ExcelImportService;
use Mehmalat\ExcelImporter\Repository\DatabaseRepository;

$app = AppFactory::create();

$app->post('/import', function ($request, $response) {
    $uploadedFiles = $request->getUploadedFiles();
    if (!isset($uploadedFiles['file'])) {
        $response->getBody()->write(json_encode(['error' => 'Datei fehlt']));
        return $response->withStatus(400)->withHeader('Content-Type', 'application/json');
    }

    $file = $uploadedFiles['file'];
    $ext = strtolower(pathinfo($file->getClientFilename(), PATHINFO_EXTENSION));
    $allowed = ['xlsx', 'xls', 'csv'];
    if (!in_array($ext, $allowed)) {
        $response->getBody()->write(json_encode(['error' => 'Nur Excel erlaubt']));
        return $response->withStatus(400)->withHeader('Content-Type', 'application/json');
    }

    $uploadDir = __DIR__ . '/../../uploads/';
    $savePath = $uploadDir . uniqid('api_', true) . '.' . $ext;
    $file->moveTo($savePath);

    try {
        $service = new ExcelImportService(new DatabaseRepository());
        $service->importFromFile($savePath);
        $response->getBody()->write(json_encode(['success' => true, 'message' => 'Import erfolgreich']));
        return $response->withHeader('Content-Type', 'application/json');
    } catch (Exception $e) {
        $response->getBody()->write(json_encode(['error' => $e->getMessage()]));
        return $response->withStatus(500)->withHeader('Content-Type', 'application/json');
    }
});

$app->run();