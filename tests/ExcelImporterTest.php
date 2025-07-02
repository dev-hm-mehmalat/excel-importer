<?php

use PHPUnit\Framework\TestCase;
use Mehmalat\ExcelImporter\ExcelImporter;

class ExcelImporterTest extends TestCase
{
    public function testImportReturnsVoid()
    {
        $importer = new ExcelImporter();
        $data = ['A1', 'B1', 'C1'];
        $result = $importer->import($data);

        $this->assertNull($result);
    }
}

