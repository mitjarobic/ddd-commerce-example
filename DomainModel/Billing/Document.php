<?php

namespace App\Modules\Commerce\DomainModel\Billing;

use App\Modules\Common\DomainModel\File\File;
use App\Modules\Common\DomainModel\File\FileId;
use Doctrine\ORM\Mapping as ORM;
use App\Modules\Commerce\DomainModel\Order\Order;
use LaravelDoctrine\Extensions\Timestamps\Timestamps;


/**
 * @ORM\Entity
 * @ORM\Table(name="documents")
 */
class Document extends File
{

    private $documentTypes = ['bill', 'prebill'];

    /**
     * @ORM\ManyToOne(targetEntity="App\Modules\Commerce\DomainModel\Order\Order", inversedBy="documents")
     */
    private $order;

    /**
     * @ORM\Column(type="string")
     */
    private $type;

    public function __construct(FileId $fileId, $type, Order $order)
    {
        parent::__construct($fileId);
        $this->setType($type);
        $this->setOrder($order);
    }

    private function setType($type)
    {
        try {
            \Assert\that($type)
                ->inArray($this->documentTypes, 'Document type is not valid!');
        } catch ( \Assert\InvalidArgumentException $e ) {
            throw new \InvalidArgumentException($e->getMessage());
        }

        $this->type = $type;
    }

    private function setOrder($order)
    {
        $this->order = $order;
    }

    /**
     * @return mixed
     */
    public function getType()
    {
        return $this->type;
    }

}