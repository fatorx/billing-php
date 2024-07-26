<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Http\Controllers\BillingCsvController;
use App\Http\Requests\UploadBillingCsvRequest;
use App\Services\BillingCsvService;
use Illuminate\Http\UploadedFile;
use Illuminate\Http\JsonResponse;
use League\Csv\Exception;
use League\Csv\InvalidArgument;
use League\Csv\SyntaxError;
use League\Csv\UnavailableStream;
use Mockery;
use Mockery\MockInterface;

class BillingCsvControllerTest extends TestCase
{
    /** @test */
    public function it_can_upload_and_process_csv_file()
    {
        $billingCsvServiceMock = Mockery::mock(BillingCsvService::class);
        $billingCsvServiceMock->shouldReceive('processCsvFile')
            ->once();
        $billingCsvServiceMock->shouldReceive('getUuidStorage')
            ->once()
            ->andReturn('some-unique-uuid');

        $this->app->instance(BillingCsvService::class, $billingCsvServiceMock);

        $file = UploadedFile::fake()->create('billings.csv', 1024);

        $request = new UploadBillingCsvRequest();
        $request->files->set('file', $file);

        $controller = new BillingCsvController($billingCsvServiceMock);

        $response = $controller->upload($request);

        $expectedResponse = [
            'data' => [
                'uuid' => 'some-unique-uuid'
            ],
            'status' => true
        ];

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(200, $response->status());
        $this->assertEquals($expectedResponse, $response->getData(true));
    }

    /** @test */
    public function it_handles_exception_when_uploading_csv_file()
    {
        $billingCsvServiceMock = Mockery::mock(BillingCsvService::class);
        $billingCsvServiceMock->shouldReceive('processCsvFile')
            ->once()
            ->andThrow(new Exception('CSV processing error'));

        $this->app->instance(BillingCsvService::class, $billingCsvServiceMock);

        $file = UploadedFile::fake()->create('billings.csv', 1024);

        $request = new UploadBillingCsvRequest();
        $request->files->set('file', $file);

        $controller = new BillingCsvController($billingCsvServiceMock);

        $this->expectException(Exception::class);

        $controller->upload($request);
    }

    /** @test */
    public function it_handles_invalid_argument_exception_when_uploading_csv_file()
    {
        $billingCsvServiceMock = Mockery::mock(BillingCsvService::class);
        $billingCsvServiceMock->shouldReceive('processCsvFile')
            ->once()
            ->andThrow(new InvalidArgument('Invalid CSV argument'));

        $this->app->instance(BillingCsvService::class, $billingCsvServiceMock);

        $file = UploadedFile::fake()->create('billings.csv', 1024);

        $request = new UploadBillingCsvRequest();
        $request->files->set('file', $file);

        $controller = new BillingCsvController($billingCsvServiceMock);

        $this->expectException(InvalidArgument::class);

        $controller->upload($request);
    }

    /** @test */
    public function it_handles_syntax_error_when_uploading_csv_file()
    {
        $billingCsvServiceMock = Mockery::mock(BillingCsvService::class);
        $billingCsvServiceMock->shouldReceive('processCsvFile')
            ->once()
            ->andThrow(new SyntaxError('CSV syntax error'));

        $this->app->instance(BillingCsvService::class, $billingCsvServiceMock);

        $file = UploadedFile::fake()->create('billings.csv', 1024);

        $request = new UploadBillingCsvRequest();
        $request->files->set('file', $file);

        $controller = new BillingCsvController($billingCsvServiceMock);

        $this->expectException(SyntaxError::class);

        $controller->upload($request);
    }
}
