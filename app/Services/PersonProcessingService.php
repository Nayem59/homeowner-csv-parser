<?php

namespace App\Services;

use App\Models\Person;
use App\DTOs\ProcessingResultData;
use App\DTOs\ProcessingErrorData;
use InvalidArgumentException;

class PersonProcessingService
{
    public function __construct(
        private PersonParserService $parserService
    ) {}

    /**
     * @param array<int, string> $rows
     * @param int $startLine
     */
    public function process(array $rows, int $startLine): ProcessingResultData
    {
        $people = [];
        $errors = [];

        foreach ($rows as $index => $row) {
            $currentLine = $startLine + $index;
            $nameInput = trim($row[0]);

            try {
                $this->validateInput($nameInput);

                foreach ($this->parserService->parse($nameInput) as $parsedPerson) {
                    $people[] = new Person($parsedPerson);
                }
            } catch (InvalidArgumentException $e) {
                $errors[] = new ProcessingErrorData(
                    $currentLine,
                    $nameInput ?: 'Empty input',
                    $e->getMessage()
                );
            }
        }

        return new ProcessingResultData(
            people: $people,
            errors: $errors
        );
    }

    /**
     * @throws InvalidArgumentException
     */
    private function validateInput(string $input): void
    {
        if ($input === '') {
            throw new InvalidArgumentException('Empty name field');
        }
    }
}
