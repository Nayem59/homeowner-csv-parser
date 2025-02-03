<?php

namespace App\DTOs;

use App\Models\Person;
use App\DTOs\ProcessingErrorData;

class ProcessingResultData
{
    public readonly int $processedCount;
    public readonly int $errorCount;

    /** 
     * @param array<Person> $people
     * @param array<ProcessingErrorData> $errors
     */
    public function __construct(
        public readonly array $people,
        public readonly array $errors
    ) {
        $this->processedCount = count($people);
        $this->errorCount = count($errors);
    }
}
