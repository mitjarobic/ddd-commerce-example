<?php


namespace App\Modules\Commerce\DomainModel\Payment\Statement;

use App\Modules\Common\DomainModel\File\File;
use App\Modules\Common\DomainModel\File\FileId;
use LaravelDoctrine\Extensions\Timestamps\Timestamps;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="sepa_xmls")
 */

class XMLSepaFile extends File
{

    /**
     * @ORM\OneToMany(targetEntity="App\Modules\Commerce\DomainModel\Payment\Statement\Statement", mappedBy="xml")
     */
    private $statement;

    public function __construct(FileId $fileId)
    {
        parent::__construct($fileId);
    }

    public function setImport($statement)
    {
        $this->statement = $statement;
    }

}