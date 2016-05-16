<?php

namespace App\Modules\Commerce\DomainModel\Order;

use Doctrine\ORM\Mapping as ORM;

/** @ORM\Embeddable */
class Status
{
    const NEWBIE = 'new';
    const FINISHED = 'finished';
    const UHFINISHED = 'unfinished';
    const CANCELED = 'canceled';
    const SHIPPED = 'shipped';

    /** @ORM\Column(type="string", length=20) */
    private $name;

    public static function newbie() {
        return new self(self::NEWBIE);
    }

    public static function finished() {
        return new self(self::FINISHED);
    }

    public static function canceled() {
        return new self(self::CANCELED);
    }

    public static function shipped() {
        return new self(self::SHIPPED);
    }

    private function __construct($anStatus) {
        $this->name = $anStatus;
    }

    public function equalsTo(self $anStatus) {
        return $this->name === (string) $anStatus;
    }

    public static function getPresentationData($status = null){

        $collection = array(
            "unfinished" => array("name"=>"nedokončano", "class"=>"inverse", "tip"=>"ni-ok"),
            "new" => array("name"=>"novo", "class"=>"", "tip"=>"ok"),
            //"obdelava" => array("name"=>"obdelava", "class"=>"info", "tip"=>"ok"),
            "finished" => array("name"=>"zaključeno", "class"=>"success", "tip"=>"ok"),
            "canceled" => array("name"=>"preklicano", "class"=>"warning", "tip"=>"ni-ok"),
        );

        return $status?$collection[$status]:$collection;

    }

}