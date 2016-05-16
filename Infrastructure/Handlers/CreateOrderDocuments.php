<?php

namespace App\Modules\Commerce\Infrastructure\Handlers;

use App\Modules\Commerce\DomainModel\Billing\BillDocumentService;
use App\Modules\Commerce\DomainModel\Billing\DocumentWasCreatedEvent;
use App\Modules\Commerce\DomainModel\Billing\PreBillDocumentService;
use App\Modules\Commerce\DomainModel\Order\OrderRepository;
use App\Modules\Commerce\DomainModel\Order\OrderWasPlacedEvent;
use App\Modules\Common\DomainModel\Event\EventDispatcher;
use App\Modules\Common\DomainModel\File\FileRepository;
use App\Modules\Common\DomainModel\Numbering\NumberService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class CreateOrderDocuments implements ShouldQueue
{

    use InteractsWithQueue, EventDispatcher;

    private $fileRepository;
    /**
     * @var OrderRepository
     */
    private $orderRepository;

    public function __construct(FileRepository $fileRepository, OrderRepository $orderRepository)
    {
        $this->fileRepository = $fileRepository;
        $this->orderRepository = $orderRepository;
    }

    /**
     * Handle the event.     *
     * @param  OrderWasPlacedEvent  $event
     */

    public function handle(OrderWasPlacedEvent $event)
    {
        $order = $this->orderRepository->findById($event->orderId);

        $documentService = $this->getDocumentManager((string)$order->getPaymentType());

        //generate pdf
        $filename = $documentService->generatePdf(['data' =>$order]);

        //create a db record
        $documentService->create($order);

        //dispatch the events
//        $events = $documentService->releaseEvents();
        $events[] = new DocumentWasCreatedEvent($event->order, $filename);
        $this->dispatch($events);
    }

    private function getDocumentManager($paymentMethod)
    {
        if ($paymentMethod == 'prebill') {
            return new PreBillDocumentService($this->fileRepository, NumberService::createYearly());
        }
        else {
            return new BillDocumentService($this->fileRepository, NumberService::createYearly());
        }
    }

}