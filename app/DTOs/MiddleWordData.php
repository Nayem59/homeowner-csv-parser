<?php

namespace App\DTOs;

class MiddleWordData
{
    public function __construct(
        public readonly ?string $initial,
        public readonly ?string $first_name
    ) {}
}
