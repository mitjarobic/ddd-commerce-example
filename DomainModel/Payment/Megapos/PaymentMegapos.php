<?php

namespace App\Modules\Commerce\DomainModel\Payment\Megapos;

use App\Modules\Commerce\DomainModel\Balance\Income;
use App\Modules\Commerce\DomainModel\Payment\Payment;
use App\Modules\Commerce\DomainModel\Payment\PaymentId;
use App\Modules\Commerce\DomainModel\Payment\PaymentType;
use Doctrine\ORM\Mapping as ORM;

/** @ORM\Entity */
class PaymentMegapos extends Payment
{
    /**
     * @ORM\Column(type="string", length=100)
     */
    private $externalStatus;

    public function __construct(PaymentId $paymentId, PaymentType $type, Income $income, $externalId, $externalStatus){
        parent::__construct($paymentId, $type, $income, $externalId);
        $this->setExternalStatus($externalStatus);
    }

    private function setExternalStatus($externalStatus)
    {
        $this->externalStatus = $externalStatus;
    }
}