<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Services\BillingCsvService;
use Illuminate\Http\UploadedFile;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Mockery;
use League\Csv\Exception;
use League\Csv\InvalidArgument;
use League\Csv\SyntaxError;
use League\Csv\UnavailableStream;

class BillingCsvControllerTest extends TestCase
{
    use RefreshDatabase;

    public function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    /** @test */
    public function it_can_upload_and_process_csv_file()
    {
        Storage::fake('local');

        $billingCsvServiceMock = Mockery::mock(BillingCsvService::class);
        $billingCsvServiceMock->shouldReceive('processCsvFile')
            ->once();
        $billingCsvServiceMock->shouldReceive('getUuidStorage')
            ->once()
            ->andReturn('some-unique-uuid');

        $this->app->instance(BillingCsvService::class, $billingCsvServiceMock);

        $file = UploadedFile::fake()->create('billings.csv', 1024);

        $response = $this->postJson(route('billing.upload'), [
            'file' => $file,
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'data' => [
                    'uuid' => 'some-unique-uuid'
                ],
                'status' => true
            ]);

        Storage::disk('local')->assertExists('uploads/' . $file->hashName());
    }

    /** @test */
    public function it_handles_exception_when_uploading_csv_file()
    {
        Storage::fake('local');

        $billingCsvServiceMock = Mockery::mock(BillingCsvService::class);
        $billingCsvServiceMock->shouldReceive('processCsvFile')
            ->once()
            ->andThrow(new Exception('CSV processing error'));

        $this->app->instance(BillingCsvService::class, $billingCsvServiceMock);

        $file = UploadedFile::fake()->create('billings.csv', 1024);

        $response = $this->postJson(route('billing.upload'), [
            'file' => $file,
        ]);

        $response->assertStatus(500);
    }

    /** @test */
    public function it_handles_invalid_argument_exception_when_uploading_csv_file()
    {
        Storage::fake('local');

        $billingCsvServiceMock = Mockery::mock(BillingCsvService::class);
        $billingCsvServiceMock->shouldReceive('processCsvFile')
            ->once()
            ->andThrow(new InvalidArgument('Invalid CSV argument'));

        $this->app->instance(BillingCsvService::class, $billingCsvServiceMock);

        $file = UploadedFile::fake()->create('billings.csv', 1024);

        $response = $this->postJson(route('billing.upload'), [
            'file' => $file,
        ]);

        $response->assertStatus(500);
    }

    /** @test */
    public function it_handles_syntax_error_when_uploading_csv_file()
    {
        Storage::fake('local');

        $billingCsvServiceMock = Mockery::mock(BillingCsvService::class);
        $billingCsvServiceMock->shouldReceive('processCsvFile')
            ->once()
            ->andThrow(new SyntaxError('CSV syntax error'));

        $this->app->instance(BillingCsvService::class, $billingCsvServiceMock);

        $file = UploadedFile::fake()->create('billings.csv', 1024);

        $response = $this->postJson(route('billing.upload'), [
            'file' => $file,
        ]);

        $response->assertStatus(500);
    }
}
