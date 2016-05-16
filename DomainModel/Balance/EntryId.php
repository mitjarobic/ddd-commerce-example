<?php

namespace App\Modules\Commerce\DomainModel\Balance;

class EntryId
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

    public function equalsTo(EntryId $aEntryId) {
        return $aEntryId->uuid() === $this->uuid();
    }

}