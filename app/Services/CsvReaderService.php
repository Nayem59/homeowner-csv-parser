<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use PhpOffice\PhpSpreadsheet\Reader\Csv;

class CsvReaderService
{
    /** @return array{'rows':array<int,string>, 'startLine':int} */
    public function read(UploadedFile $file, bool $hasHeader): array
    {
        $reader = new Csv();
        $reader->setReadEmptyCells(false);

        $spreadsheet = $reader->load($file->getPathname());
        $rows = $spreadsheet->getActiveSheet()->toArray();

        $startLine = 1;
        if ($hasHeader) {
            array_shift($rows);
            $startLine = 2;
        }

        return [
            'rows' => $rows,
            'startLine' => $startLine,
        ];
    }
}
