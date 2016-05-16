<?php


namespace App\Modules\Commerce\DomainModel\Payment\Iban;


use App\Jobs\Job;

use App\Modules\Commerce\DomainModel\Balance\Income;
use App\Modules\Commerce\DomainModel\Payment\PaymentRepository;
use App\Modules\Common\DomainModel\Money;
use Exception;
use Illuminate\Http\Request;

class CreateAPaymentIbanJob extends Job
{
    /**
     * @var
     */
    private $amount;
    /**
     * @var
     */
    private $externalId;
    /**
     * @var
     */
    private $reference;
    /**
     * @var
     */
    private $iban;

    /**
     * Create a new job instance.
     *
     * @param $amount
     * @param $externalId
     * @param $reference
     * @param $iban
     */
    public function __construct($amount, $externalId, $reference, $iban)
    {
        $this->amount = $amount;
        $this->externalId = $externalId;
        $this->reference = $reference;
        $this->iban = $iban;
    }

    /**
     * Execute the job.
     *
     * @param PaymentRepository $paymentRepository
     * @throws Exception
     */
    public function handle(PaymentRepository $paymentRepository)
    {
        try{

            $paymentIban = new PaymentIban(
                $paymentRepository->nextIdentity(),
                new Income(new Money($this->amount)),
                $this->externalId,
                $this->reference,
                $this->iban
            );
            $paymentRepository->add($paymentIban);

        }catch (Exception $e){

            throw $e;

        }
    }

    public static function fromRequest(Request $request)
    {
        $params = array_keys_cc_to_sc($request->all());
        return new self(
            $params['amount'],
            $params['externalId'],
            $params['reference'],
            $params['iban']
        );
    }
}