<?php

namespace App\DTOs;

class PersonData
{
    public function __construct(
        public readonly string $title,
        public readonly ?string $first_name,
        public readonly ?string $initial,
        public ?string $last_name
    ) {}
}
