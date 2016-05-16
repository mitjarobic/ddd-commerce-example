<?php

namespace App\Modules\Commerce\DomainModel\Cart;

use App\Modules\NSK\DomainModel\BenefitVariation\BenefitVariationRepository;
use Moltin\Cart\Cart as MoltinCart;

class Cart
{
    private $cart;
    private $nskGroupId = null;
    private $benefitVariationRepository;

    public function __construct(MoltinCart $cart, BenefitVariationRepository $benefitVariationRepository)
    {
        $this->cart = $cart;
        $this->benefitVariationRepository = $benefitVariationRepository;
    }

    public function add(Cartable $product)
    {
        $class = class_basename($product);
        $product = $product->toCartArray();

        if($class == 'CardVariation'){
            $this->nskGroupId = $product["nsk_group_id"];
        }

        $this->cart->insert($product);
    }

    public function totalItems($uniqueItems = false)
    {
        return $this->cart->totalItems($uniqueItems);
    }

    public function total($withTax = true)
    {
        return $this->cart->total($withTax);
    }

    public function benefitsOnCard(){

        if(!$this->nskGroupId) return [];

        return $this
            ->benefitVariationRepository
            ->getListOnCard($this->nskGroupId);
    }

    public function contents(){

        $contents = $this->cart->contents(true);

        $result = [];
        $result['card'] = array_where($contents, function($key, $value)
        {
            return starts_with($value['id'], 'card');
        });
        $result['benefit'] = array_where($contents, function($key, $value)
        {
            return starts_with($value['id'], 'benefit');
        });
        $result['benefitOnCard'] = $this->benefitsOnCard();

        return $result;

    }
}