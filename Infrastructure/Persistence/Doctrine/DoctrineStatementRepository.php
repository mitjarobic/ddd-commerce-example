<?php

namespace App\Modules\Commerce\Infrastructure\Persistence\Doctrine;

use App\Modules\Commerce\DomainModel\Payment\Statement\Statement;
use App\Modules\Commerce\DomainModel\Payment\Statement\StatementId;
use App\Modules\Commerce\DomainModel\Payment\Statement\StatementRepository;

use Carbon\Carbon;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Mapping\ClassMetadata;
use Faker\Provider\Uuid;

class DoctrineStatementRepository extends EntityRepository implements StatementRepository
{
    public function __construct(EntityManager $em, ClassMetadata $class ){
        parent::__construct($em, $class);
    }

    public function nextIdentity() {
        return StatementId::create(
            strtoupper(Uuid::uuid())
        );
    }

    public function add(Statement $aStatement)
    {
        $this->_em->persist($aStatement);
    }

    public function remove(Statement $aStatement)
    {
        $this->_em->remove($aStatement);
    }

    public function findById($anId)
    {
        return $this->find($anId);
    }

    public function getDatatableData($dateFrom = null, $dateTo = null)
    {
        $qb = $this->_em->createQueryBuilder();
        $qb->select('s.id, s.GrpHdr_MsgId, s.GrpHdr_CreDtTm, s.Stmt_Id, s.Stmt_LglSeqNb, s.Stmt_CreDtTm, COUNT(p.id) as num_payments, x.path as file, x.name as fileName')
            ->from('App\Modules\Commerce\DomainModel\Payment\Statement\Statement', 's')
            ->leftJoin('s.payments', 'p')
            ->innerJoin('s.xml', 'x')
            ->orderBy('s.GrpHdr_CreDtTm, s.Stmt_CreDtTm', 'ASC')
            ->groupBy('s.id, x.path, x.name');

        if($dateFrom && $dateTo){
            $dateTo = Carbon::createFromFormat('Y-m-d', $dateTo)->addDay(1)->format('Y-m-d');
            $qb->andWhere($qb->expr()->between('s.createdAt', ':from', ':to'))
                ->setParameter('from', $dateFrom)
                ->setParameter('to', $dateTo);
        }

        return collect($qb->getQuery()->getScalarResult());
    }
}