<?php

namespace App\Modules\Commerce\Infrastructure\Handlers;

use App\Modules\Commerce\DomainModel\Billing\BillDocumentService;
use App\Modules\Commerce\DomainModel\Billing\DocumentRepository;
use App\Modules\Commerce\DomainModel\Billing\DocumentWasCreatedEvent;
use App\Modules\Commerce\DomainModel\Billing\PreBillDocumentService;
use App\Modules\Commerce\DomainModel\Order\OrderWasPlacedEvent;
use App\Modules\Common\DomainModel\Event\EventDispatcher;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class CreateADocument implements ShouldQueue
{

    use InteractsWithQueue, EventDispatcher;

    private $documentRepository;

    public function __construct(DocumentRepository $documentRepository)
    {
        $this->documentRepository = $documentRepository;
    }

    /**
     * Handle the event.     *
     * @param  OrderWasPlacedEvent  $event
     */

    public function handle(OrderWasPlacedEvent $event)
    {
        $documentService = $this->getDocumentManager($event->order['payment_type']);

        //create a db record
        $documentService->create($event->order['id']);

        //generate pdf
        $filename = $documentService->generatePdf($event->order);

        //dispatch the events
        $events = $documentService->releaseEvents();
        $events[] = new DocumentWasCreatedEvent($event->order, $filename);
        $this->dispatch($events);
    }

    private function getDocumentManager($paymentMethod)
    {
        if ($paymentMethod == 'prebill') {
            return new PreBillDocumentService($this->documentRepository, NumberManager::createYearly());
        }
        else {
            return new BillDocumentService($this->documentRepository, NumberManager::createYearly());
        }
    }

}