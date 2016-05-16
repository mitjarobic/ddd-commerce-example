<?php

namespace App\Modules\Commerce\DomainModel\Billing;


use App\Modules\Common\DomainModel\Event\EventCollector;
use App\Modules\Common\DomainModel\File\CustomFileInfo;
use App\Modules\Common\DomainModel\File\FileRepository;
use App\Modules\Common\DomainModel\Numbering\NumberService;
use Gedmo\Uploadable\UploadableListener;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Config;

abstract class DocumentService
{
    protected $pdf;
    protected $type;
    protected $number;
    protected $fileRepository;
    protected $numberService;
    private $tempFolder;
    private $filesFolder;

    abstract protected function getNumber();
    abstract protected function getFileName();

    public function __construct(FileRepository $fileRepository, NumberService $numberService = null)
    {
        $this->tempFolder = storage_path(Config::get('nsk.storage.temp'));
        $this->filesFolder = storage_path(Config::get('nsk.storage.files'));
        $this->pdf = App::make('dompdf.wrapper');
        $this->fileRepository = $fileRepository;
        $this->setNumberService($numberService);
    }

    private function setNumberService(NumberService $numberService = null)
    {
        if (!$numberService)
            $numberService = NumberService::createYearly();
        $this->numberService = $numberService;
    }

    protected function setNumber($number)
    {
        $this->number = $number;
    }

    public function generatePdf($data, $number = null)
    {
        if($number){
            $this->setNumber($number);
        }

        $filename = $this->getFileName();

        $this->pdf
            ->loadView('pdf.'.$this->type, $data)
            ->save($this->tempFolder.$filename);

        return $filename;
    }

    public function create($order)
    {
        $listener = app(UploadableListener::class);
        $listener->setDefaultPath($this->filesFolder);
        $document = new Document($this->fileRepository->nextIdentity(), $this->type, $order);
        $listener->addEntityFileInfo($document, new CustomFileInfo($this->tempFolder.$this->getFileName()));
        $this->fileRepository->add($document);
//        dd($document);
        return $document;
    }

}