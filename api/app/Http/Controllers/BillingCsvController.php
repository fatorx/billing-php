<?php

namespace App\Http\Controllers;

use App\Http\Requests\UploadBillingCsvRequest;
use App\Logs\Log;
use App\Services\BillingCsvService;
use Exception as GenericException;
use Illuminate\Http\JsonResponse;

class BillingCsvController extends Controller
{
    use Log;

    const ERROR_PROCESSOR = 'Error processing file.';

    protected BillingCsvService $billingCsvService;

    public function __construct(BillingCsvService $billingCsvService)
    {
        $this->billingCsvService = $billingCsvService;
    }

    /**
     * Handle the file upload and process the CSV.
     *
     * @param UploadBillingCsvRequest $request
     * @return JsonResponse
     */
    public function upload(UploadBillingCsvRequest $request): JsonResponse
    {
        try {
            $uuid = $this->billingCsvService->processCsvFile($request->file('file'));
            $responseData = [
                'data' => [
                    'uuid' => $uuid
                ],
                'status' => true
            ];

            return response()->json($responseData, 200);

        } catch (GenericException $e) {
            $this->addLog($e);

            $responseData = [
                'data' => [
                    'message' => self::ERROR_PROCESSOR,
                ],
                'status' => false
            ];

            return response()->json($responseData, 400);
        }
    }
}
