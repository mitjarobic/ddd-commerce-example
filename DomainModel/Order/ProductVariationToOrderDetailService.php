<?php

namespace App\Modules\Commerce\DomainModel\Order;

use App\Modules\Commerce\DomainModel\Cart\Cartable;

class ProductVariationToOrderDetailService implements VariationToDetail
{
    private $repository;

    public function __construct(OrderRepository $orderRepository){
        $this->repository = $orderRepository;
    }

    public function transform(Cartable $product){

        $className = $this->getClassName($product);
        $product = $product->toCartArray();

        if($className == 'CardVariation'){
            return new OrderDetailCard(
                $this->repository->nextODIdentity(),
                $product['id'],
                $product['name'],
                $product['price'],
                $product['quantity'],
                $product['type']
            );
        }else if($className == 'BenefitVariation'){
            return new OrderDetailBenefit(
                $this->repository->nextODIdentity(),
                $product['id'],
                $product['name'],
                $product['price'],
                $product['quantity'],
                $product['description'],
                $product['discount'],
                $product['on_card']
            );
        }

    }

    /**
     * @param $product
     * @return array|mixed
     */
    private function getClassName($product)
    {
        $class = explode('\\', get_class($product));
        $class = end($class);
        return $class;
    }
}