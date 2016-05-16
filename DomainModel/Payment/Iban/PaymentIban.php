<?php

namespace App\Modules\Commerce\DomainModel\Payment\Iban;

use App\Modules\Commerce\DomainModel\Balance\Income;
use App\Modules\Commerce\DomainModel\Payment\Payment;
use App\Modules\Commerce\DomainModel\Payment\PaymentId;
use App\Modules\Commerce\DomainModel\Payment\PaymentType;
use App\Modules\Commerce\DomainModel\Payment\Statement\Statement;
use Doctrine\ORM\Mapping as ORM;

/** @ORM\Entity */
class PaymentIban extends Payment
{

    /**
     * @ORM\ManyToOne(targetEntity="App\Modules\Commerce\DomainModel\Payment\Statement\Statement", inversedBy="payments")
     */
    private $statement;

    /**
     * @ORM\Column(type="string", length=100)
     */
    private $reference;

    /**
     * @ORM\Column(type="string", length=20)
     */
    private $iban;

    public function __construct(PaymentId $paymentId, Income $income, $externalId, $reference, $iban){
        parent::__construct($paymentId, PaymentType::prebill(), $income, $externalId);
        $this->setReference($reference);
        $this->setIban($iban);
    }

    private function setReference($reference)
    {
        $this->reference = $reference;
    }

    private function setIban($iban)
    {
        $this->iban = $iban;
    }

    public function setStatement(Statement $statement)
    {
        $this->statement = $statement;
    }

    /**
     * @return mixed
     */
    public function getReference()
    {
        return $this->reference;
    }

}