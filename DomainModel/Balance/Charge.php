<?php

namespace App\Modules\Commerce\DomainModel\Balance;


use App\Modules\Common\DomainModel\Money;
use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use LaravelDoctrine\Extensions\Timestamps\Timestamps;

/**
 * @ORM\Entity
 * @ORM\Table(name="charges")
 */
class Charge
{

    use Timestamps;

    const STATUS_OPEN = 'open';
    const STATUS_BINDED = 'binded';
    const STATUS_BINDED_PARTIAL = 'binded_partial';

    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    protected $id;


    /**
     * @ORM\Column(type="money")
     */
    private $amount;

    /**
     * @ORM\Column(type="datetime")
     */
    private $date;

    /**
     * @ORM\Column(type="string", length=50)
     */
    private $status;

    /**
     * @ORM\OneToMany(targetEntity="App\Modules\Commerce\DomainModel\Balance\Entry", mappedBy="charge")
     */
    private $entries;

    /**
     * @ORM\OneToOne(targetEntity="App\Modules\Commerce\DomainModel\Order\Order", mappedBy="charge")
     **/
    private $order;

    public function __construct(Money $amount, Datetime $date = null)
    {
        $this->amount = $amount;
        $this->date = $date ? $date : new Datetime();
        $this->status = self::STATUS_OPEN;
        $this->entries = new ArrayCollection();
    }

    /**
     * @return Money
     */
    public function getAmount()
    {
        return $this->amount;
    }

    /**
     * @return Money
     */

    public function getAvailableAmount()
    {
        return $this->amount->sub($this->getBindedAmount());
    }

    /**
     * @return Money
     */
    private function getBindedAmount()
    {
        $sum = new Money(0);
        $iterator = $this->entries->getIterator();

        while ($iterator->valid()) {
            $amount = $iterator->current()->getAmount();
            $sum = $sum->add($amount);
            $iterator->next();
        }

        return $sum;
    }

    private function updateStatus()
    {
        $state = $this->amount->sub($this->getBindedAmount());

        if($this->getBindedAmount()->amount() == 0){
            $this->status = self::STATUS_OPEN;
        }
        else if($state->amount() == 0){
            $this->status = self::STATUS_BINDED;
        }
        else{
            $this->status = self::STATUS_BINDED_PARTIAL;
        }
    }

    public function addEntry(Entry $entry)
    {
        $this->entries->add($entry);
        $this->updateStatus();
    }

    /**
     * @return mixed
     */
    public function getStatus()
    {
        return $this->status;
    }

    public static function getPresentationData($status)
    {
        $collection = array(
            "open" => array("name"=>"ni plačano", "class"=>"danger"),
            "binded_partial" => array("name"=>"delno plačano", "class"=>"wraning"),
            "binded" => array("name"=>"plačano", "class"=>"primary"),
        );

        return $collection[$status];
    }
}