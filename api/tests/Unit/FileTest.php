<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\File;
use Illuminate\Foundation\Testing\RefreshDatabase;

class FileTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_can_create_a_file()
    {
        $fileData = [
            'uuid' => '1',
            'process' => 1,
            'num_lines' => 100,
            'num_lines_processed' => 50,
            'finish' => 0,
        ];

        $file = File::create($fileData);

        $this->assertDatabaseHas('files', $fileData);
        $this->assertInstanceOf(File::class, $file);
        $this->assertEquals($fileData['uuid'], $file->uuid);
        $this->assertEquals($fileData['process'], $file->process);
        $this->assertEquals($fileData['num_lines'], $file->num_lines);
        $this->assertEquals($fileData['num_lines_processed'], $file->num_lines_processed);
        $this->assertEquals($fileData['finish'], $file->finish);
    }

    /** @test */
    public function it_can_find_a_file_by_id()
    {
        $fileData = [
            'uuid' => '1',
            'process' => 1,
            'num_lines' => 100,
            'num_lines_processed' => 50,
            'finish' => 0,
        ];

        $fileData = File::create($fileData);
        $file = File::findOrFail($fileData->id);

        $this->assertInstanceOf(File::class, $file);
        $this->assertEquals($fileData['uuid'], $file->uuid);

        $file->delete();
    }

    /** @test */
    public function it_can_update_a_file()
    {
        $fileData = [
            'uuid' => '1',
            'process' => 1,
            'num_lines' => 100,
            'num_lines_processed' => 50,
            'finish' => 0,
        ];

        $file = File::create($fileData);

        $updatedData = [
            'num_lines_processed' => 100,
            'finish' => 1,
        ];

        $file->update($updatedData);

        $this->assertDatabaseHas('files', array_merge($fileData, $updatedData));
    }

    /** @test */
    public function it_can_delete_a_file()
    {
        $fileData = [
            'uuid' => '1',
            'process' => 1,
            'num_lines' => 100,
            'num_lines_processed' => 50,
            'finish' => 0,
        ];

        $file = File::create($fileData);

        $file->delete();

        $this->assertDatabaseMissing('files', $fileData);
    }
}
