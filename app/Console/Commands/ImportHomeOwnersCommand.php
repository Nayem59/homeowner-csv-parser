<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\CsvReaderService;
use App\Services\PersonProcessingService;
use App\DTOs\ProcessingResultData;
use Illuminate\Http\UploadedFile;
use Symfony\Component\HttpFoundation\File\File;

class ImportHomeOwnersCommand extends Command
{
    protected $signature = 'homeowners:import 
                            {file : Path to CSV file}
                            {--has-header : Whether the CSV contains a header row}';

    protected $description = 'Import homeowners from CSV file';

    public function __construct(
        private CsvReaderService $csvReaderService,
        private PersonProcessingService $personProcessingService
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        $this->info('----------- Command Execution Started -----------');
        try {
            $filePath = $this->argument('file');

            if (!file_exists($filePath)) {
                $this->error("File not found: $filePath");
                return 1;
            }

            $file = new File($filePath);
            $uploadedFile = new UploadedFile(
                $file->getPathname(),
                $file->getFilename(),
                $file->getMimeType(),
                null,
                true
            );

            $hasHeader = $this->option('has-header');

            $csvData = $this->csvReaderService->read(
                $uploadedFile,
                $hasHeader
            );

            /** @var ProcessingResultData $result */
            $result = $this->personProcessingService->process(
                $csvData['rows'],
                $csvData['startLine']
            );

            $this->displayResults($result);
            $this->info('----------- Command Execution Completed -----------');
            return 0;
        } catch (\Exception $e) {
            $this->error("Error processing file: " . $e->getMessage());
            return 1;
        }
    }

    private function displayResults(ProcessingResultData $result): void
    {
        if ($result->processedCount > 0) {
            $this->info("\nSuccessfully processed {$result->processedCount} records:");
            $this->table(
                ['Title', 'First Name', 'Initial', 'Last Name'],
                array_map(fn($p) => [
                    $p->title,
                    $p->first_name ?? '-',
                    $p->initial ?? '-',
                    $p->last_name
                ], $result->people),
                'box-double'
            );
        }

        if ($result->errorCount > 0) {
            $this->error("\nEncountered {$result->errorCount} errors:");
            $this->table(
                ['Row', 'Input', 'Error'],
                array_map(fn($e) => [
                    $e->row,
                    $e->input,
                    $e->message
                ], $result->errors),
                'box-double'
            );
        }

        $this->line("Total Processed: <fg=green>{$result->processedCount}</>");
        $this->line("Total Errors: <fg=red>{$result->errorCount}</>");
    }
}
