<?php

namespace App\Modules\Commerce\DomainModel\Billing;


use App\Modules\Common\DomainModel\File\FileRepository;
use App\Modules\Common\DomainModel\Numbering\NumberService;

class BillDocumentService extends DocumentService
{
    public function __construct(FileRepository $fileRepository, NumberService $numberService = null)
    {
        parent::__construct($fileRepository, $numberService);
        $this->type = 'bill';
        $this->setNumber($this->numberService->generate($this->type));
    }

    protected function getNumber()
    {
        return date('Y')."/".str_pad($this->number, 8, '0', STR_PAD_LEFT);
    }

    protected function getFileName()
    {
        return str_replace('/', '_', $this->getNumber()).'.pdf';
    }

}