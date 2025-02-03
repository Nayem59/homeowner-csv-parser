<?php

namespace App\Http\Controllers;

use App\Http\Requests\CsvUploadRequest;
use Illuminate\Http\UploadedFile;
use App\Services\CsvReaderService;
use App\Services\PersonProcessingService;
use Illuminate\Http\JsonResponse;
use Exception;

class PersonController extends Controller
{
    public function __construct(
        private CsvReaderService $csvReaderService,
        private PersonProcessingService $personProcessingService
    ) {}

    public function uploadHomeOwners(CsvUploadRequest $request): JsonResponse
    {
        try {
            $file = $request->file('file');

            if (empty($file) || !$file instanceof UploadedFile) {
                throw new Exception("Invalid file upload");
            }

            $hasHeader = $request->boolean('csvHasHeader');

            $csvData = $this->csvReaderService->read($file, $hasHeader);

            $processingResult = $this->personProcessingService->process(
                $csvData['rows'],
                $csvData['startLine']
            );

            $response = [
                'message' => $processingResult->errorCount > 0 ? 'Partial content processed' : 'All records processed successfully',
                'processed_count' => $processingResult->processedCount,
                'error_count' => $processingResult->errorCount,
                'data' => $processingResult->people,
            ];

            if ($processingResult->errorCount > 0) {
                $response['errors'] = $processingResult->errors;
                return response()->json($response, 207);
            }

            return response()->json($response);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'File processing failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
