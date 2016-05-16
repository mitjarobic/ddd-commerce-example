<?php

namespace App\Modules\Commerce\Infrastructure\Persistence\Doctrine;

use App\Modules\Commerce\DomainModel\Billing\Document;
use App\Modules\Commerce\DomainModel\Billing\DocumentId;
use App\Modules\Commerce\DomainModel\Billing\DocumentRepository;
use App\Modules\Common\DomainModel\File\File;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Mapping\ClassMetadata;
use Faker\Provider\Uuid;

class DoctrineDocumentRepository extends EntityRepository implements DocumentRepository
{
    public function __construct(EntityManager $em, ClassMetadata $class ){
        parent::__construct($em, $class);
    }

    public function nextIdentity() {
        return DocumentId::create(
            strtoupper(Uuid::uuid())
        );
    }

    public function add($aDocument)
    {
        $this->_em->persist($aDocument);
    }

    public function findById($anId)
    {
        return $this->find($anId);
    }
}