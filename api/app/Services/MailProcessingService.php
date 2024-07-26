<?php

namespace App\Services;

use App\Logs\Log;
use App\Models\Billing;
use Exception;

class MailProcessingService
{
    use Log;

    const DEBIT_ID_NOT_PROVIDED = 'process_invoice';

    protected Billing $billingModel;

    public function __construct(Billing $billingModel)
    {
        $this->billingModel = $billingModel;
    }

    /**
     * @throws Exception
     */
    public function processMessage(string $message): void
    {
        $data = json_decode($message, true);

        if (isset($data['uuid']) && isset($data['debit_id'])) {
            $this->processLine($data['uuid'], $data['debit_id']);
        } else {
            $e = new Exception(self::DEBIT_ID_NOT_PROVIDED);
            $this->addLog($e);
            throw $e;
        }
    }

    /**
     * @throws Exception
     */
    protected function processLine(string $uuid, string $debitId): void
    {
        print('Search and Sending DEBIT_ID: ' . $debitId);
    }
}
