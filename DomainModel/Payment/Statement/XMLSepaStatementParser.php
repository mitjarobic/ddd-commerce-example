<?php


namespace App\Modules\Commerce\DomainModel\Payment\Statement;


class XMLSepaStatementParser
{
    /**
     * @var array
     */
    private $statement;

    public function __construct(array $statement){

        $this->statement = $statement;
    }

    public function getParams()
    {
        return [
            'Id'=>array_get($this->statement, 'Id'),
            'LglSeqNb'=>array_get($this->statement, 'LglSeqNb'),
            'ElctrncSeqNb'=>array_get($this->statement, 'ElctrncSeqNb'),
            'CreDtTm'=>array_get($this->statement, 'CreDtTm'),
        ];

    }

    public function getPaymentsData(){

        $entries = array_get($this->statement, 'Ntry');

        if($this->hasOnlyOneEntry($entries))
            $entries = [$entries];

        return $this->getEntriesCollection($entries);
    }

    /**
     * @param $entries
     * @return bool
     */
    private function hasOnlyOneEntry($entries)
    {
        return array_values($entries) !== $entries;
    }

    /**
     * @param $entries
     * @return array
     */
    private function getEntriesCollection($entries)
    {
        $collection = [];
        foreach ($entries as $entry) {

            //CRDT = priliv
            //DBIT = odliv
            $entryType = array_get($entry, 'CdtDbtInd');
            if($entryType != 'CRDT') continue;

            $reversed = array_get($entry, 'RvslInd');
            if($reversed === 'true' || $reversed === true){
                continue;
            }

            $txDetails = array_get($entry, 'NtryDtls.TxDtls', array());
            $payer = array_get($txDetails, 'RltdPties.Dbtr');

            $collection[] = [
                'id' => array_get($entry, 'AcctSvcrRef'),
                'amount' => array_get($entry, 'Amt'),
                'date' => array_get($entry, 'BookgDt.Dt'),
                'reference' => array_get($txDetails, 'RmtInf.Strd.CdtrRefInf.Ref', 'ni bilo podano'),
                'iban' => array_get($txDetails, 'RltdPties.DbtrAcct.Id.IBAN', 'ni bilo podano'),
                'payer' => [
                    'name' => array_get($payer,'Nm'),
                    'address' => array_get($payer,'PstlAdr.AdrLine.0'),
                    'city' => array_get($payer,'PstlAdr.AdrLine.1'),
                    'country' => array_get($payer,'PstlAdr.Ctry'),
                ]
            ];
        }

        return $collection;
    }

}