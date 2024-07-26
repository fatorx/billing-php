<?php

namespace App\Services;

use App\Logs\Log;
use App\Models\Billing;
use Exception;

class InvoiceProcessingService
{
    use Log;

    const DEBIT_ID_NOT_PROVIDED = 'ID do débito não fornecido!';
    const DEBIT_NOT_FOUND = 'Débito não encontrado!';
    const QUEUE_NAME = 'process_mail';

    protected QueueService $queueService;
    protected Billing $billingModel;

    public function __construct(QueueService $queueService, Billing $billingModel)
    {
        $this->queueService = $queueService;
        $this->billingModel = $billingModel;
    }

    /**
     * @throws Exception
     */
    public function processMessage(string $message): void
    {
        $data = json_decode($message, true);

        if (isset($data['uuid']) && isset($data['debit_id'])) {
            $uuid = $data['uuid'];
            $debitId = $data['debit_id'];
            $this->processInvoice($uuid, $debitId);
        } else {
            $e = new Exception(self::DEBIT_ID_NOT_PROVIDED);
            $this->addLog($e);
            throw $e;
        }
    }

    /**
     * @throws Exception
     */
    protected function processInvoice(string $uuid, int $debitId): void
    {
        $debitData = $this->getDebt($debitId);

        if ($debitData) {
            $this->generatePdfDebit($debitData);
            $this->generateQueueMessage($uuid, $debitId);
        } else {
            $e = new Exception(self::DEBIT_NOT_FOUND);
            $this->addLog($e);
            throw $e;
        }
    }

    public function generatePdfDebit($debitData): void
    {
        // @todo : generate pdf debit
        print('Generate PDF DEBIT_ID: ' . $debitData->id);
    }

    /**
     * @param $id
     * @return mixed
     */
    public function getDebt($id): mixed
    {
        return $this->billingModel->find($id);
    }

    protected function generateQueueMessage(string $uuid, int $debitId): void
    {
        $message = [
            'uuid' => $uuid,
            'debit_id' => $debitId,
        ];

        $this->queueService->publishMessage($message, self::QUEUE_NAME);
    }
}
