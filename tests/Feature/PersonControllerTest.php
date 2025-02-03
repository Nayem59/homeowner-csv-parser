<?php

namespace Tests\Feature;

use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class PersonControllerTest extends TestCase
{
    /** @param string[] $rows */
    protected function csvData(array $rows): UploadedFile
    {
        $csvLines = [];

        foreach ($rows as $row) {
            $quotedRow = '"' . $row . '"';
            $csvLines[] = $quotedRow;
        }

        $csvContent = implode("\n", $csvLines);

        return UploadedFile::fake()->createWithContent('test.csv', $csvContent);
    }

    public function test_it_accepts_valid_csv_with_header(): void
    {
        $file = $this->csvData([
            'Homeowners',
            'Mr John Smith',
            'Mrs Faye Hughes-Eastwood',
            'Dr P. Gunn',
        ]);

        $response = $this->postJson('/upload', [
            'file' => $file,
            'csvHasHeader' => true
        ]);

        $response->assertOk()
            ->assertJsonStructure([
                'message',
                'processed_count',
                'error_count',
                'data' => [
                    ['title', 'first_name', 'initial', 'last_name']
                ]
            ])
            ->assertJsonCount(3, 'data');
    }

    public function test_it_handles_missing_header_properly(): void
    {
        $file = $this->csvData([
            'Mr Tom Staff',
            'Mrs Jane Smith',
        ]);

        $response = $this->postJson('/upload', [
            'file' => $file,
            'csvHasHeader' => false
        ]);

        $response->assertOk()
            ->assertJsonCount(2, 'data');
    }

    public function test_it_returns_errors_for_invalid_rows(): void
    {
        $file = $this->csvData([
            'Homeowners',
            'Invalid Title',
            'Mr A. Candidate',
            'Ms Claire Robbo'
        ]);

        $response = $this->postJson('/upload', [
            'file' => $file,
            'csvHasHeader' => true
        ]);

        $response->assertStatus(207)
            ->assertJsonStructure([
                'errors' => [
                    ['row', 'input', 'message']
                ]
            ])
            ->assertJsonCount(1, 'errors');
    }

    public function test_it_validates_file_type(): void
    {
        $file = UploadedFile::fake()->create('document.pdf', 100);

        $response = $this->postJson('/upload', [
            'file' => $file,
            'csvHasHeader' => true
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['file']);
    }

    public function test_it_handles_empty_file(): void
    {
        $file = $this->csvData([]);

        $response = $this->postJson('/upload', [
            'file' => $file,
            'csvHasHeader' => true
        ]);

        $response->assertStatus(500)
            ->assertServerError();
    }

    public function test_it_handles_partial_success(): void
    {
        $file = $this->csvData([
            'Mr Valid User',
            'Invalid Row',
            'Dr M. McStuffins'
        ]);

        $response = $this->postJson('/upload', [
            'file' => $file,
            'csvHasHeader' => false
        ]);

        $response->assertStatus(207)
            ->assertJson([
                'processed_count' => 2,
                'error_count' => 1
            ]);
    }

    public function test_it_handles_different_name_formats(): void
    {
        $file = $this->csvData([
            'Mr Tom Staff',
            'Mrs F. Smith',
            'Dr A. Charles',
            'Prof Alex Brogan'
        ]);

        $response = $this->postJson('/upload', [
            'file' => $file,
            'csvHasHeader' => false
        ]);

        $response->assertOk()
            ->assertJsonCount(4, 'data')
            ->assertJsonFragment([
                'title' => 'Prof',
                'first_name' => 'Alex',
                'last_name' => 'Brogan'
            ]);
    }

    public function test_it_handles_multiple_people_in_single_row(): void
    {
        $file = $this->csvData([
            'Mr and Mrs Smith',
            'Dr & Mrs Joe Bloggs'
        ]);

        $response = $this->postJson('/upload', [
            'file' => $file,
            'csvHasHeader' => false
        ]);

        $response->assertOk()
            ->assertJsonCount(4, 'data');
    }
}
