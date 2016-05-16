<?php

namespace App\Modules\Commerce\DomainModel\Payment;

use App\Jobs\Job;

class DeleteAPaymentJob extends Job
{
    private $id;

    /**
     * Create a new job instance.
     *
     * @param $id
     */
    public function __construct($id)
    {
        $this->id = $id;
    }

    /**
     * Execute the job.
     *
     * @param PaymentRepository $paymentRepository
     */
    public function handle(PaymentRepository $paymentRepository)
    {
        $payment = $paymentRepository->findById($this->id);
        $paymentRepository->remove($payment);
    }
}