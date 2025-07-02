<?php

namespace Mehmalat\ExcelImporter;

use PDO;

class ExcelImporter
{
    protected ?PDO $pdo = null;
    protected bool $mockMode;

    public function __construct()
    {
        $this->mockMode = ($_ENV['DB_MOCK'] ?? 'false') === 'true';

        if (!$this->mockMode) {
            $host = $_ENV['DB_HOST'];
            $db   = $_ENV['DB_DATABASE'];
            $user = $_ENV['DB_USERNAME'];
            $pass = $_ENV['DB_PASSWORD'];
            $charset = 'utf8mb4';

            $dsn = "mysql:host=$host;dbname=$db;charset=$charset";
            $options = [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            ];

            try {
                $this->pdo = new PDO($dsn, $user, $pass, $options);
            } catch (\PDOException $e) {
                die("❌ DB-Verbindung fehlgeschlagen: " . $e->getMessage());
            }
        }
    }

    public function import(array $row): void
    {
        if ($this->mockMode) {
            echo "🔹 Zeile gelesen: " . json_encode($row) . PHP_EOL;
        } else {
            // Beispiel: Nur wenn mindestens 2 Spalten da sind
            if (count($row) >= 2) {
                $stmt = $this->pdo->prepare("INSERT INTO tabelle (spalte1, spalte2) VALUES (?, ?)");
                $stmt->execute([$row[0], $row[1]]);
            }
        }
    }
}

