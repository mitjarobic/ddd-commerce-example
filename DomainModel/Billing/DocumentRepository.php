<?php

namespace App\Modules\Commerce\DomainModel\Billing;

interface DocumentRepository
{
    public function add($aDocument);
    public function nextIdentity();
}