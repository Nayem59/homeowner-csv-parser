<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use App\Services\PersonParserService;
use App\DTOs\PersonData;

class PersonParserServiceTest extends TestCase
{
    private PersonParserService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new PersonParserService();
    }

    public function test_parses_single_person_with_first_name(): void
    {
        $result = $this->service->parse('Mr John Smith');
        $this->assertEquals([
            new PersonData(
                title: 'Mr',
                first_name: 'John',
                initial: null,
                last_name: 'Smith'
            )
        ], $result);
    }

    public function test_parses_single_person_with_last_name_only(): void
    {
        $result = $this->service->parse('Mrs Smith');
        $this->assertEquals([
            new PersonData(
                title: 'Mrs',
                first_name: null,
                initial: null,
                last_name: 'Smith'
            )
        ], $result);
    }

    public function test_parses_period_after_initial(): void
    {
        $result = $this->service->parse('Mr M. Mackie');
        $this->assertEquals([
            new PersonData(
                title: 'Mr',
                first_name: null,
                initial: 'M',
                last_name: 'Mackie'
            )
        ], $result);
    }

    public function test_parses_initial_without_period(): void
    {
        $result = $this->service->parse('Dr P Gunn');
        $this->assertEquals([
            new PersonData(
                title: 'Dr',
                first_name: null,
                initial: 'P',
                last_name: 'Gunn'
            )
        ], $result);
    }

    public function test_parses_hyphenated_last_name(): void
    {
        $result = $this->service->parse('Mrs Faye Hughes-Eastwood');
        $this->assertEquals([
            new PersonData(
                title: 'Mrs',
                first_name: 'Faye',
                initial: null,
                last_name: 'Hughes-Eastwood'
            )
        ], $result);
    }

    public function test_parses_mr_and_mrs(): void
    {
        $result = $this->service->parse('Mr and Mrs Smith');
        $this->assertEquals([
            new PersonData(
                title: 'Mr',
                first_name: null,
                initial: null,
                last_name: 'Smith'
            ),
            new PersonData(
                title: 'Mrs',
                first_name: null,
                initial: null,
                last_name: 'Smith'
            )
        ], $result);
    }

    public function test_parses_mr_and_mrs_with_ampersand(): void
    {
        $result = $this->service->parse('Dr & Mrs Joe Bloggs');
        $this->assertEquals([
            new PersonData(
                title: 'Dr',
                first_name: null,
                initial: null,
                last_name: 'Bloggs'
            ),
            new PersonData(
                title: 'Mrs',
                first_name: 'Joe',
                initial: null,
                last_name: 'Bloggs'
            )
        ], $result);
    }

    public function test_parses_two_full_names(): void
    {
        $result = $this->service->parse('Mr Tom Staff and Mr John Doe');
        $this->assertEquals([
            new PersonData(
                title: 'Mr',
                first_name: 'Tom',
                initial: null,
                last_name: 'Staff'
            ),
            new PersonData(
                title: 'Mr',
                first_name: 'John',
                initial: null,
                last_name: 'Doe'
            )
        ], $result);
    }

    public function test_handles_alternative_title_formats(): void
    {
        $result = $this->service->parse('Mister John Doe');
        $this->assertEquals([
            new PersonData(
                title: 'Mr',
                first_name: 'John',
                initial: null,
                last_name: 'Doe'
            )
        ], $result);
    }

    public function test_handles_professional_titles(): void
    {
        $result = $this->service->parse('Prof Alex Brogan');
        $this->assertEquals([
            new PersonData(
                title: 'Prof',
                first_name: 'Alex',
                initial: null,
                last_name: 'Brogan'
            )
        ], $result);
    }

    public function test_handles_ms_title(): void
    {
        $result = $this->service->parse('Ms Claire Robbo');
        $this->assertEquals([
            new PersonData(
                title: 'Ms',
                first_name: 'Claire',
                initial: null,
                last_name: 'Robbo'
            )
        ], $result);
    }

    public function test_handles_more_then_two_people(): void
    {
        $result = $this->service->parse('Mr Tom Staff and Mr John Doe & Mrs Jane Smith');
        $this->assertEquals([
            new PersonData(
                title: 'Mr',
                first_name: 'Tom',
                initial: null,
                last_name: 'Staff'
            ),
            new PersonData(
                title: 'Mr',
                first_name: 'John',
                initial: null,
                last_name: 'Doe'
            ),
            new PersonData(
                title: 'Mrs',
                first_name: 'Jane',
                initial: null,
                last_name: 'Smith'
            )
        ], $result);
    }

    public function test_handles_lower_case_input(): void
    {
        $result = $this->service->parse('mr tom staff');
        $this->assertEquals([
            new PersonData(
                title: 'Mr',
                first_name: 'Tom',
                initial: null,
                last_name: 'Staff'
            )
        ], $result);
    }

    public function test_handles_middle_name(): void
    {
        $result = $this->service->parse('Ms Claire Jane Robbo');
        $this->assertEquals([
            new PersonData(
                title: 'Ms',
                first_name: 'Claire Jane',
                initial: null,
                last_name: 'Robbo'
            )
        ], $result);
    }

    public function test_handles_empty_string(): void
    {
        $result = $this->service->parse('');
        $this->assertEquals([], $result);
    }

    public function test_handles_whitespace_only(): void
    {
        $result = $this->service->parse('   ');
        $this->assertEquals([], $result);
    }

    public function test_handles_missing_required_fields(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->service->parse('John');
    }
}
