<?php

namespace App\Modules\Commerce\DomainModel\Payment\Statement;


class StatementId
{
    private $uuid;

    private function __construct($anUuid) {
        $this->uuid = $anUuid;
    }

    public static function create($anUuid) {
        return new static($anUuid);
    }

    public function uuid() {
        return $this->uuid;
    }

    public function equalsTo(StatementId $statementId) {
        return $statementId->uuid() === $this->uuid();
    }
}