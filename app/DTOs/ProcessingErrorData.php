<?php

namespace App\DTOs;

class ProcessingErrorData
{
    public function __construct(
        public readonly int $row,
        public readonly string $input,
        public readonly string $message
    ) {}
}
