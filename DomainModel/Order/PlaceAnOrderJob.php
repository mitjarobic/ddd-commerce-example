<?php

namespace App\Modules\Commerce\DomainModel\Order;

use App\Jobs\Job;
use App\Modules\Commerce\DomainModel\Balance\Charge;
use App\Modules\Commerce\DomainModel\Payment\PaymentType;
use App\Modules\Common\DomainModel\Address\Address;
use App\Modules\Common\DomainModel\Event\EventDispatcher;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Http\Request;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;


class PlaceAnOrderJob extends Job implements ShouldQueue{

    use InteractsWithQueue, SerializesModels, EventDispatcher;

    private $user;
    private $products;
    private $charge;
    private $shipping;
    private $payer;
    private $paymentType;
    private $comment;

    /**
     * Create a new job instance.
     * @param $user
     * @param array $products
     * @param Charge $charge
     * @param Address $shipping
     * @param Payer $payer
     * @param PaymentType $paymentType
     * @param null $comment
     */
    public function __construct($user, array $products, Charge $charge, Address $shipping, Payer $payer, PaymentType $paymentType, $comment = null)
    {
        $this->user = $user;
        $this->products = $products;
        $this->charge = $charge;
        $this->shipping = $shipping;
        $this->payer = $payer;
        $this->paymentType = $paymentType;
        $this->comment = $comment;
    }

    public static function fromRequest(Request $request){
        return true;
    }

    public function handle(OrderRepository $orderRepository){

        $order = new Order(
            $orderRepository->nextIdentity(),
            $this->user,
            $this->products,
            $this->charge,
            $this->shipping,
            $this->payer,
            $this->paymentType,
            $this->comment
        );

        //place an order
        $orderRepository->add($order);
        //$events = $order->releaseEvents();

        $events[] = new OrderWasPlacedEvent($order->dbId());
        $this->dispatch($events);

    }

}