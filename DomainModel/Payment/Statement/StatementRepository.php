<?php

namespace App\Modules\Commerce\DomainModel\Payment\Statement;

interface StatementRepository
{
    public function nextIdentity();
    public function add(Statement $aStatement);
    public function remove(Statement $aStatement);
}