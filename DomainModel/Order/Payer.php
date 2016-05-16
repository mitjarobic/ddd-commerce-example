<?php

namespace App\Modules\Commerce\DomainModel\Order;

use App\Modules\Common\DomainModel\Address\Address;
use Doctrine\ORM\Mapping as ORM;

/** @ORM\Embeddable */
class Payer
{

    /** @ORM\Column(type="string", length=255) */
    private $name;

    /** @ORM\Column(type="string", length=255) */
    private $street;

    /** @ORM\Column(type="string", length=10) */
    private $zip;

    /** @ORM\Column(type="string", length=255) */
    private $city;

    /** @ORM\Column(type="string", length=255) */
    private $country;

    /** @ORM\Column(type="string", length=20) */
    private $vat;

    private function __construct(Address $address, $vat = null){
        $this->name = $address->getName();
        $this->street = $address->getStreet();
        $this->zip = $address->getZip();
        $this->city = $address->getCity();
        $this->country = $address->getCountry();
        $this->setVat($vat);
    }

    public static function asBusiness($name, $street, $zip, $city, $country, $vat){
        $address = Address::asBusiness($name, $street, $zip, $city, $country);
        return new self($address, $vat);
    }

    public static function asPerson($name, $surname, $street, $zip, $city, $country){
        $address = Address::asPerson($name, $surname, $street, $zip, $city, $country);
        return new self($address);
    }

    private function setVat($vat){
        $this->validateVat($vat);
        $this->vat = $vat;
    }

    private function validateVat($vat){
        return true;
    }

    public function getVat(){
        return $this->vat;
    }

}