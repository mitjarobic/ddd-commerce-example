<?php

namespace App\Modules\Commerce\DomainModel\Order;

use App\Modules\Commerce\DomainModel\Balance\Charge;
use App\Modules\Commerce\DomainModel\Balance\Entry;
use App\Modules\Commerce\DomainModel\Balance\EntryRepository;
use App\Modules\Commerce\DomainModel\Balance\Incomeable;
use App\Modules\Commerce\DomainModel\Payment\PaymentType;
use App\Modules\Common\DomainModel\Address\Address;
use App\Modules\Common\DomainModel\Event\EventCollector;
use App\Modules\Common\DomainModel\IdentifiableDomainObject;
use App\Modules\Common\DomainModel\Numbering\NumberService;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use LaravelDoctrine\Extensions\Timestamps\Timestamps;


/**
 * @ORM\Entity
 * @ORM\Table(name="orders")
 */
class Order extends IdentifiableDomainObject
{
    use EventCollector;
    use Timestamps;

    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    protected $id;

    private $numberService;
    private $orderDetailsService;

    /** @ORM\ManyToMany(targetEntity="OrderDetail", cascade={"persist", "remove"}) */
    private $details;

    /** @ORM\Column(type="string", length=20) */
    private $number;

    /** @ORM\Embedded(class="App\Modules\Common\DomainModel\Address\Address") */
    private $shipping;

    /** @ORM\Embedded(class="App\Modules\Commerce\DomainModel\Order\Payer") */
    private $payer;

    /** @ORM\Column(type="integer") */
    private $user_id;

    /** @ORM\Embedded(class="App\Modules\Commerce\DomainModel\Order\Status") */
    private $status;

    /**
     * @ORM\OneToOne(targetEntity="App\Modules\Commerce\DomainModel\Balance\Charge", inversedBy="order", cascade={"persist", "remove"})
     **/
    private $charge;

    /** @ORM\Column(type="string", length=20) */
    private $reference;

    /** @ORM\Column(type="text", nullable=true) */
    private $comment;

    /** @ORM\Column(type="text", nullable=true) */
    private $adminComment;

    /** @ORM\Embedded(class="App\Modules\Commerce\DomainModel\Payment\PaymentType") */
    private $paymentType;

    /**
     * @ORM\OneToMany(targetEntity="App\Modules\Commerce\DomainModel\Billing\Document", mappedBy="order")
     */
    private $documents;

    private $entryRepository;

    /**
     * @param OrderId $orderId
     * @param $customer
     * @param array $products
     * @param Charge $charge
     * @param Address $shipping
     * @param Payer $payer
     * @param PaymentType $paymentType
     * @param string $comment
     */

    public function __construct(OrderId $orderId, $customer, array $products, Charge $charge, Address $shipping, Payer $payer, PaymentType $paymentType, $comment = null){

        $this->setNumberService();
        $this->setTransformService();
        $this->setEntryRepository();
        $this->details = new ArrayCollection();

        $this->setId($orderId);
        $this->setCustomer($customer);
        $this->setDetails($products);
        $this->setStatus(Status::newbie());
        $this->setShipping($shipping);
        $this->setPayer($payer);
        $this->setCharge($charge);
        $this->setComment($comment);
        $this->setPaymentType($paymentType);
        $this->setNumber();
        $this->setReference();

//        $this->raiseEvent(new OrderWasPlacedEvent());

    }

    public function setNumberService(NumberService $numberService = null){
        if (!$numberService)
            $numberService = NumberService::createYearly();
        $this->numberService = $numberService;
    }

    public function setTransformService(VariationToDetail $orderDetailsService = null){
        if (!$orderDetailsService)
            $orderDetailsService = app('App\Modules\Commerce\DomainModel\Order\ProductVariationToOrderDetailService');
        $this->orderDetailsService = $orderDetailsService;
    }


    public function setDetails(array $products)
    {
        foreach($products as $product){
            $this->details->add($this->orderDetailsService->transform($product));
        }
    }

    public function details()
    {
        return $this->details;
    }

    public function pay(Incomeable $payment)
    {
       return Entry::paymentIsEqualOrGreaterThanCharge(
           $this->getEntryRepository()->nextIdentity(),
           $this->charge,
           $payment->getIncome()
       );

    }

    public function getBalanceStatus()
    {
        return $this->charge->getStatus();
    }

    private function setEntryRepository()
    {
        $this->entryRepository = app(EntryRepository::class);
    }

    private function getEntryRepository()
    {
        if(!$this->entryRepository)
            $this->setEntryRepository();
        return $this->entryRepository;
    }

    private function setNumber()
    {
        $this->number = $this->createNumber();
    }

    private function createNumber()
    {
        return date('Y')."/".str_pad($this->numberService->generate('order'), 8, '0', STR_PAD_LEFT);
    }

    public function setReference($reference = null)
    {
        $this->reference = $reference? $reference:$this->createReference();
    }

    private function createReference()
    {

        //primer telekoma: SI12 2121271131493

        $stevilka= $this->number;
        $sklic = str_replace('/0', '0', $stevilka);

        //izracun kontrolne stevilke
        $ponder = 13;
        $sestevek_zmozkov = 0;
        for($i=0; $i < strlen($sklic); $i++){

            $sestevek_zmozkov += $ponder * $sklic[$i];
            $ponder--;
            /*echo "<br/>";
           echo $sestevek_zmozkov*/;
        }

        $kontrolna_stevilka = 11 - ($sestevek_zmozkov%11);

        if($kontrolna_stevilka == 10 || $kontrolna_stevilka == 11)$kontrolna_stevilka = 0;

        return "SI12 " . $sklic . $kontrolna_stevilka;

    }

    private function setShipping($shipping)
    {
        $this->shipping = $shipping;
    }

    private function setPayer($payer)
    {
        $this->payer = $payer;
    }

    private function setCustomer($customer)
    {
        $userId = is_object($customer) ? $customer->id : $customer;
        $this->user_id = $userId;
    }

    private function setStatus(Status $status)
    {
        $this->status = $status;
    }

    private function setCharge(Charge $charge)
    {
        $this->charge = $charge;
    }


    private function setComment($comment)
    {
        if($comment){
            $this->comment = $comment;
        }
    }

    public function setAdminComment($comment)
    {
        $this->adminComment = $comment;
    }

    private function setPaymentType($payment)
    {
        $this->paymentType = $payment;
    }

    /**
     * @return mixed
     */
    public function getPaymentType()
    {
        return $this->paymentType;
    }

    /**
     * @return mixed
     */
    public function getCharge()
    {
        return $this->charge;
    }





}