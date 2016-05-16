<?php

namespace App\Modules\Commerce\DomainModel\Balance;


interface EntryRepository
{
    public function add(Entry $aEntry);
    public function remove(Entry $aEntry);
    public function nextIdentity();
}