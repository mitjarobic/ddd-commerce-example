<?php

namespace App\Modules\Commerce\Infrastructure\Persistence\Doctrine;


use App\Modules\Commerce\DomainModel\Balance\Entry;
use App\Modules\Commerce\DomainModel\Balance\EntryId;
use App\Modules\Commerce\DomainModel\Balance\EntryRepository;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Mapping\ClassMetadata;
use Faker\Provider\Uuid;

class DoctrineEntryRepository extends EntityRepository implements EntryRepository
{

    public function __construct(EntityManager $em, ClassMetadata $class ){
        parent::__construct($em, $class);
    }

    public function nextIdentity() {
        return EntryId::create(
            strtoupper(Uuid::uuid())
        );
    }

    public function add(Entry $aEntry)
    {
        $this->_em->persist($aEntry);
    }

    public function remove(Entry $aEntry)
    {
        $this->_em->persist($aEntry);
    }

    public function findById($anId)
    {
        return $this->find($anId);
    }

}