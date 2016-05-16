<?php


namespace App\Modules\Commerce\DomainModel\Payment\Statement;

use App\Modules\Common\DomainModel\IdentifiableDomainObject;
use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use LaravelDoctrine\Extensions\Timestamps\Timestamps;

/**
 * @ORM\Entity
 * @ORM\Table(name="statements")
 */
class Statement extends IdentifiableDomainObject
{

    use Timestamps;

    /** @ORM\OneToMany(targetEntity="App\Modules\Commerce\DomainModel\Payment\Iban\PaymentIban", mappedBy="statement", cascade={"persist", "remove"}) */
    private $payments;

    /**
     * @ORM\ManyToOne(targetEntity="App\Modules\Commerce\DomainModel\Payment\Statement\XMLSepaFile", inversedBy="statement", cascade={"persist", "remove"})
     */
    private $xml;

    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    protected $id;

    /** @ORM\Column(length=100, options={"comment":"Enolični Identifikator paketa"}) */
    private $GrpHdr_MsgId;

    /** @ORM\Column(type="date", options={"comment":"Datum in čas nastanka paketa"}) */
    private $GrpHdr_CreDtTm;

    /** @ORM\Column(length=100, options={"comment":"Enolična identifikacija podatkov o prometu"} ) */
    private $Stmt_Id;

    /** @ORM\Column(type="integer", nullable=true, options={"comment":"Zaporedna številka elektronskega izpiska, ki ni nujno enaka zaporedni številki papirnega izpiska"}) */
    private $Stmt_ElctrncSeqNb;

    /** @ORM\Column(type="integer", options={"comment":"Zaporedna številka izpiska, ki je enaka na papirnem izpisku"}) */
    private $Stmt_LglSeqNb;

    /** @ORM\Column(type="date", options={"comment":"Datum in čas nastanka izpiska"}) */
    private $Stmt_CreDtTm;


    /**
     * @param StatementId $statementId
     * @param $GrpHdr_MsgId
     * @param DateTime $GrpHdr_CreDtTm
     * @param $Stmt_Id
     * @param $Stmt_LglSeqNb
     * @param $Stmt_ElctrncSeqNb
     * @param $Stmt_CreDtTm
     * @param array $payments
     * @param XMLSepaFile $file
     */
    public function __construct($statementId, $GrpHdr_MsgId, DateTime $GrpHdr_CreDtTm, $Stmt_Id, $Stmt_LglSeqNb, $Stmt_ElctrncSeqNb, $Stmt_CreDtTm, array $payments, XMLSepaFile $file){
        $this->payments = new ArrayCollection();
        $this->setId($statementId);
        $this->GrpHdr_MsgId = $GrpHdr_MsgId;
        $this->GrpHdr_CreDtTm = $GrpHdr_CreDtTm;
        $this->Stmt_Id = $Stmt_Id;
        $this->Stmt_LglSeqNb = $Stmt_LglSeqNb;
        $this->Stmt_CreDtTm = $Stmt_CreDtTm;
        $this->Stmt_ElctrncSeqNb = $Stmt_ElctrncSeqNb;
        $this->setPayments($payments);
        $this->xml = $file;
    }

    private function setPayments(array $payments)
    {
        foreach($payments as $payment){
            $payment->setStatement($this);
            $this->payments->add($payment);
        }
    }

    /**
     * @return mixed
     */
    public function getPayments()
    {
        return $this->payments;
    }


}