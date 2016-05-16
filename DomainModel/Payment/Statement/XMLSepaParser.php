<?php


namespace App\Modules\Commerce\DomainModel\Payment\Statement;


use Exception;

class XMLSepaParser
{
    private $document;

    /**
     * @param $filePath
     */
    public function __construct($filePath)
    {
        if(!$this->fileIsValid($filePath))
            $this->handleBadFile($filePath);
    }

    /**
     * @return array XMLSepaStatementParser
     */
    public function getStatementParsers()
    {
        $statements = array_get($this->document, 'Stmt');

        if($this->hasOnlyOneStatement($statements))
            $statements = [$statements];

        return $this->getStatementParsersCollection($statements);
    }


    public function getHeaderData()
    {
        return [
            'MsgId' => array_get($this->document, 'GrpHdr.MsgId'),
            'CreDtTm' => array_get($this->document, 'GrpHdr.CreDtTm'),
        ];
    }

    private function transformFileToArray($filePath)
    {
        $xml = simplexml_load_file($filePath);
        $json = json_encode($xml);
        return json_decode($json,TRUE);
    }

    private function fileIsValid($filePath)
    {
        $fileAsArray = $this->transformFileToArray($filePath);

        if(!$this->document = array_get($fileAsArray, 'BkToCstmrStmt'))
           return false;

        return true;
    }

    private function handleBadFile()
    {
        throw new Exception('Sepa XML format is not valid!', 101);
    }
  
    /**
     * @param $statements
     * @return bool
     */
    private function hasOnlyOneStatement($statements)
    {
        return array_values($statements) !== $statements;
    }

    /**
     * @param $statements
     * @return array
     */
    private function getStatementParsersCollection($statements)
    {
        $collection = [];
        foreach ($statements as $statement) {
            $collection[] = new XMLSepaStatementParser($statement);
        }

        return $collection;
    }

}