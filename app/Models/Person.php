<?php

namespace App\Models;

use App\DTOs\PersonData;

class Person
{
  public string $title;
  public string|null $initial;
  public string|null $first_name;
  public string|null $last_name;

  public function __construct(PersonData $person)
  {
    $this->title = $person->title;
    $this->initial = $person->initial ?? null;
    $this->first_name = $person->first_name ?? null;
    $this->last_name = $person->last_name ?? null;
  }
}
