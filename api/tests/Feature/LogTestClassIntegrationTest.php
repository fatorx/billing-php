<?php

namespace Tests\Feature;

use Tests\TestCase;
use Tests\Unit\LogTestClass;
use Exception;
use Illuminate\Support\Facades\Storage;

class LogTestClassIntegrationTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();
        Storage::fake('logs');
    }

    /** @test */
    public function it_can_add_log_for_exception()
    {
        $exception = new Exception('Test exception');
        $logTestClass = new LogTestClass();
        $logTestClass->pathLogs = Storage::disk('logs')->path('');

        $logTestClass->triggerException($exception);

        $logFile = 'warning_' . now()->format('Y-m-d-H') . '.txt';
        Storage::disk('logs')->assertExists($logFile);

        $logContent = Storage::disk('logs')->get($logFile);
        $this->assertStringContainsString('Test exception', $logContent);
    }

    /** @test */
    public function it_can_add_log_message()
    {
        $message = 'Test log message';
        $logTestClass = new LogTestClass();
        $logTestClass->pathLogs = Storage::disk('logs')->path('');

        $logTestClass->triggerMessage($message);

        $logFile = 'info_' . now()->format('Y-m-d-H') . '.txt';
        Storage::disk('logs')->assertExists($logFile);

        $logContent = Storage::disk('logs')->get($logFile);
        $this->assertStringContainsString('Test log message', $logContent);
    }
}
