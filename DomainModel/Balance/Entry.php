<?php

namespace App\Modules\Commerce\DomainModel\Balance;

use App\Modules\Common\DomainModel\IdentifiableDomainObject;
use App\Modules\Common\DomainModel\Money;
use Doctrine\ORM\Mapping as ORM;
use Exception;
use LaravelDoctrine\Extensions\Timestamps\Timestamps;

/**
 * @ORM\Entity
 * @ORM\Table(name="charge_income")
 */
class Entry extends IdentifiableDomainObject
{

    use Timestamps;

    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    protected $id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Modules\Commerce\DomainModel\Balance\Income", inversedBy="entries", cascade={"persist"})
     */
    private $income;

    /**
     * @ORM\ManyToOne(targetEntity="App\Modules\Commerce\DomainModel\Balance\Charge", inversedBy="entries", cascade={"persist"})
     */
    private $charge;

    /**
     * @ORM\Column(type="money")
     */
    private $amount;


    public function __construct(EntryId $entryId, Charge $charge, Income $income, Money $amount = null){

        $this->validate($charge, $income, $amount);

        $this->setId($entryId);
        $this->charge = $charge;
        $this->income = $income;

        if(!$amount){
            $amount = $this->getMaxAvailableAmountForEntry();
        }

        $this->amount = $amount;
        $this->charge->addEntry($this);
        $this->income->addEntry($this);

    }

    private function getMaxAvailableAmountForEntry()
    {
        return $this->charge->getAvailableAmount() > $this->income->getAvailableAmount() ?
            $this->income->getAvailableAmount() : $this->charge->getAvailableAmount();
    }

    private function validate(Charge $charge, Income $income, Money $amount = null)
    {

        if($charge->getAvailableAmount()->amount() == 0){
            throw new Exception('Charge is binded');
        }
        if($income->getAvailableAmount()->amount() == 0){
            throw new Exception('Income is binded');
        }

        if($amount){
            if($charge->getAvailableAmount()->amount() < $amount->amount() || $income->getAvailableAmount()->amount() < $amount->amount())
                throw new \DomainException;
        }
    }

    /**
     * @return Money
     */
    public function getAmount()
    {
        return $this->amount;
    }

    /**
     * @return mixed
     */
    public function getIncome()
    {
        return $this->income;
    }

    /**
     * @return mixed
     */
    public function getCharge()
    {
        return $this->charge;
    }

//    public static function paymentIsEqualToCharge(EntryId $entryId, Charge $charge, Income $income, Money $amount = null){
//        if($charge->getAmount() != $income->getAmount()){
//            throw new BalanceBindAmountsException('Income amount must be te same as Charge amount!');
//        }
//        return new self($entryId, $charge, $income, $amount);
//    }

    public static function paymentIsEqualOrGreaterThanCharge(EntryId $entryId, Charge $charge, Income $income, Money $amount = null){
        if($charge->getAmount()->amount() < $income->getAmount()->amount()){
            throw new BalanceBindAmountsException('Income amount must be greater or same as Charge amount!');
        }
        return new self($entryId, $charge, $income, $amount);
    }


}