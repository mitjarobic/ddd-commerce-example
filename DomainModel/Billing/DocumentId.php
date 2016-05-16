<?php

namespace App\Modules\Commerce\DomainModel\Billing;


class DocumentId
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

    public function equalsTo(DocumentId $aDocumentId) {
        return $aDocumentId->uuid() === $this->uuid();
    }
}