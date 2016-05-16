<?php


namespace App\Modules\Commerce\DomainModel\Payment\Megapos;

use App\Jobs\Job;
use App\Modules\Commerce\DomainModel\Balance\Income;
use Exception;
use Illuminate\Http\Request;

class CreateAPaymentMegaposJob extends Job
{
    /**
     * @var
     */
    private $amount = null;
    /**
     * @var
     */
    private $externalId = null;
    /**
     * @var
     */
    private $status = null;
    /**
     * @var
     */
    private $type;


    /**
     * Create a new job instance.
     *
     * @param $amount
     * @param $externalId
     * @param $status
     */
    public function __construct($type, $amount, $externalId, $status)
    {
        $this->amount = $amount;
        $this->externalId = $externalId;
        $this->status = $status;
        $this->type = $type;
    }

    /**
     * Execute the job.
     *
     * @param PaymentMegaposRepository $paymentMegaposRepository
     * @throws Exception
     */
    public function handle(PaymentMegaposRepository $paymentMegaposRepository)
    {
        try{

            $paymentMegapos = new PaymentMegapos(
                $paymentMegaposRepository->nextIdentity(),
                $this->type,
                new Income($this->amount),
                $this->externalId,
                $this->status
            );
            $paymentMegaposRepository->add($paymentMegapos);

        }catch (Exception $e){

            throw $e;

        }
    }

    public static function fromRequest(Request $request)
    {
        $params = array_keys_cc_to_sc($request->all());
        return new self(
            $params['type'],
            $params['amount'],
            $params['externalId'],
            $params['status']
        );
    }
}