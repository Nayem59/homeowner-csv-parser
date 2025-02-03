<?php

namespace App\Services;

use App\DTOs\MiddleWordData;
use App\DTOs\PersonData;
use InvalidArgumentException;

class PersonParserService
{
    /** @var array<string, string> */
    private array $titleMappings = [
        'mr' => 'Mr',
        'mrs' => 'Mrs',
        'ms' => 'Ms',
        'miss' => 'Miss',
        'dr' => 'Dr',
        'prof' => 'Prof',
        'mister' => 'Mr',
        'mistress' => 'Mrs',
    ];

    /** @var array<string> */
    private array $conjunctions = ['and', '&'];

    /**
     * @return array<PersonData>
     * @throws InvalidArgumentException
     */
    public function parse(string $input): array
    {
        $input = trim($input);
        if ($input === '') {
            return [];
        }

        $parts = $this->splitIntoParts($input);
        $people = [];

        foreach ($parts as $partWords) {
            $people[] = $this->parsePart($partWords);
        }

        $this->inheritLastName($people);
        $this->validatePeople($people);

        return $people;
    }

    /**
     * @return array<array<string>>
     */
    private function splitIntoParts(string $input): array
    {
        $words = array_values(array_filter(explode(' ', $input)));

        $parts = [];
        $currentPart = [];
        $conjunctions = array_map('strtolower', $this->conjunctions);

        foreach ($words as $word) {
            if (in_array(strtolower($word), $conjunctions, true)) {
                if ($currentPart !== []) {
                    $parts[] = $currentPart;
                    $currentPart = [];
                }
            } else {
                $currentPart[] = $word;
            }
        }

        if ($currentPart !== []) {
            $parts[] = $currentPart;
        }

        return $parts;
    }

    /**
     * @param array<string> $words
     * @throws InvalidArgumentException
     */
    private function parsePart(array $words): PersonData
    {
        if ($words === []) {
            throw new InvalidArgumentException('Empty name part');
        }

        $titleWord = array_shift($words);
        $title = $this->normalizeTitle($titleWord);

        $last_name = $words !== []
            ? $this->formatName(array_pop($words))
            : null;

        $result = $this->processMiddleWords($words);

        return new PersonData(
            $title,
            $result->first_name,
            $result->initial,
            $last_name
        );
    }

    /**
     * @param array<string> $words
     */
    private function processMiddleWords(array $words): MiddleWordData
    {
        $initial = null;
        $firstNameParts = [];

        foreach ($words as $word) {
            $trimmed = rtrim($word, '.');
            if (ctype_alpha($trimmed) && strlen($trimmed) === 1) {
                $initial = strtoupper($trimmed);
                break;
            }
            $firstNameParts[] = $this->formatName($word);
        }

        return new MiddleWordData(
            $initial,
            $firstNameParts !== [] ? implode(' ', $firstNameParts) : null
        );
    }

    private function formatName(string $name): string
    {
        return implode('-', array_map(
            fn(string $part) => ucfirst(strtolower($part)),
            explode('-', $name)
        ));
    }

    /**
     * @param array<PersonData> $people
     */
    private function inheritLastName(array &$people): void
    {
        $last_name = null;

        foreach (array_reverse($people, true) as $i => $person) {
            if ($person->last_name !== null) {
                $last_name = $person->last_name;
            } elseif ($last_name !== null) {
                $people[$i]->last_name = $last_name;
            }
        }
    }

    /**
     * @param array<PersonData> $people
     * @throws InvalidArgumentException
     */
    private function validatePeople(array $people): void
    {
        foreach ($people as $person) {
            if ($person->last_name === null) {
                throw new InvalidArgumentException('Missing required last name');
            }
        }
    }

    /**
     * @throws InvalidArgumentException
     */
    private function normalizeTitle(string $title): string
    {
        $lowerTitle = strtolower($title);
        if (isset($this->titleMappings[$lowerTitle])) {
            return $this->titleMappings[$lowerTitle];
        }

        throw new InvalidArgumentException("Invalid title: $title");
    }
}
