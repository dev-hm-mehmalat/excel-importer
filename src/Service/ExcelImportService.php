<?php
namespace Mehmalat\ExcelImporter\Service;

use PhpOffice\PhpSpreadsheet\IOFactory;
use Mehmalat\ExcelImporter\Repository\DatabaseRepository;

class ExcelImportService
{
    private $repository;

    public function __construct(DatabaseRepository $repository)
    {
        $this->repository = $repository;
    }

    public function importFromFile($filePath)
    {
        $spreadsheet = IOFactory::load($filePath);
        $sheet = $spreadsheet->getActiveSheet();

        foreach ($sheet->getRowIterator() as $row) {
            $data = [];
            foreach ($row->getCellIterator() as $cell) {
                $data[] = $cell->getFormattedValue();
            }
            $this->repository->save($data);
        }
    }
}
