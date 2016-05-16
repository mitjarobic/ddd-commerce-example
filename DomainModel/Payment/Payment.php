<?php

namespace App\Modules\Commerce\DomainModel\Payment;


use App\Modules\Commerce\DomainModel\Balance\Income;
use App\Modules\Commerce\DomainModel\Balance\Incomeable;
use App\Modules\Common\DomainModel\IdentifiableDomainObject;
use Doctrine\ORM\Mapping as ORM;
use LaravelDoctrine\Extensions\Timestamps\Timestamps;


/**
 * This class represents a OrderDetail item, either a CardVariationDetail or an BenefitVariationDetail.
 * @ORM\Entity
 * @ORM\Table(name="payments")
 * @ORM\InheritanceType("JOINED")
 * @ORM\DiscriminatorColumn(name="discriminator", type="string")
 * @ORM\DiscriminatorMap( {"iban" = "App\Modules\Commerce\DomainModel\Payment\Iban\PaymentIban", "megapos" = "App\Modules\Commerce\DomainModel\Payment\Megapos\PaymentMegapos"} )
 */

abstract class Payment extends IdentifiableDomainObject implements Incomeable
{

    use Timestamps;

    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    protected $id;

    /**
     * @ORM\OneToOne(targetEntity="App\Modules\Commerce\DomainModel\Balance\Income", inversedBy="payment", cascade={"persist", "remove"})
     **/
    private $income;

    /** @ORM\Embedded(class="App\Modules\Commerce\DomainModel\Payment\PaymentType") */
    protected $type;

    /** @ORM\Column(type="string", length=50) */
    private $externalId;

    public function __construct(PaymentId $paymentId, PaymentType $type, Income $income, $externalId){
        $this->setId($paymentId);
        $this->setType($type);
        $this->setIncome($income);
        $this->setExternalId($externalId);
    }

    private function setType($type)
    {
        $this->type = $type;
    }

    private function setIncome($income)
    {
        $this->income = $income;
    }

    private function setExternalId($externalId)
    {
        $this->externalId = $externalId;
    }

    /**
     * @return mixed
     */
    public function getIncome()
    {
        return $this->income;
    }

}