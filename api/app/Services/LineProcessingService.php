<?php

namespace App\Services;

use App\Logs\Log;
use App\Models\Billing;
use Exception;

class LineProcessingService
{
    use Log;

    const MESSAGE_EXCEPTION_DEBT_CHECK = 'Débito já registrado.';
    const MESSAGE_EXCEPTION_VALUE = 'Valor da fatura inválido.';
    const INVALID_LINE_FORMAT = 'Invalid line format.';
    const QUEUE_NAME = 'process_invoice';
    const UUID_OR_LINE_NOT_PROVIDED = 'UUID or line not provided in the message.';

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

        if (isset($data['uuid']) && isset($data['line'])) {
            $this->processLine($data['uuid'], $data['line']);
        } else {
            $e = new Exception(self::UUID_OR_LINE_NOT_PROVIDED);
            $this->addLog($e);
            throw $e;
        }
    }

    /**
     * @throws Exception
     */
    protected function processLine(string $uuid, string $line): void
    {
        $lineData = str_getcsv($line);

        if (count($lineData) !== 6) {
            throw new Exception(self::INVALID_LINE_FORMAT);
        }

        $debitId = (int)$lineData[5];
        $governmentId = $lineData[1];
        $email = $lineData[2];
        $name = $lineData[0];
        $amount = (float)$lineData[3];
        $dueDate = $lineData[4];

        if (!$this->checkDebtId($debitId)) {
            $e = new Exception(self::MESSAGE_EXCEPTION_DEBT_CHECK);
            $this->addLog($e);
            throw $e;
        }

        if ($amount == 0) {
            $e = new Exception(self::MESSAGE_EXCEPTION_VALUE);
            $this->addLog($e);
            throw $e;
        }

        $this->registerBilling($debitId, $governmentId, $email, $name, $amount, $dueDate);

        $newMessage = ['uuid' => $uuid, 'debit_id' => $debitId];
        $this->queueService->publishMessage($newMessage, self::QUEUE_NAME);
    }

    /**
     * @param $id
     * @return bool
     */
    public function checkDebtId($id): bool
    {
        $invoiceCheck = $this->billingModel->find($id);
        return ($invoiceCheck === null);
    }

    /**
     * @param mixed $debitId
     * @param mixed $governmentId
     * @param mixed $email
     * @param mixed $name
     * @param mixed $amount
     * @param mixed $dueDate
     * @return void
     */
    public function registerBilling(
        int $debitId, string $governmentId, string $email,
        string $name, string $amount, string $dueDate
    ): void
    {
        $billingData = [
            'id' => $debitId,
            'government_id' => $governmentId,
            'email' => $email,
            'name' => $name,
            'amount' => $amount,
            'due_date' => $dueDate,
            'status' => 'pending',
        ];

        $this->billingModel->create($billingData);
    }
}
