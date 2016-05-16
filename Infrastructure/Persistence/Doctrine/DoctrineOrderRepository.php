<?php

namespace App\Modules\Commerce\Infrastructure\Persistence\Doctrine;

use App\Modules\Commerce\DomainModel\Balance\Charge;
use App\Modules\Commerce\DomainModel\Order\Order;
use App\Modules\Commerce\DomainModel\Order\OrderDetailId;
use App\Modules\Commerce\DomainModel\Order\OrderId;
use App\Modules\Commerce\DomainModel\Order\OrderRepository;
use Carbon\Carbon;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Mapping\ClassMetadata;
use Faker\Provider\Uuid;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;


class DoctrineOrderRepository extends EntityRepository implements OrderRepository
{

    public function __construct(EntityManager $em, ClassMetadata $class ){
        parent::__construct($em, $class);
    }

    public function nextIdentity() {
        return OrderId::create(
            strtoupper(Uuid::uuid())
        );
    }

    public function nextODIdentity() {
        return OrderDetailId::create(
            strtoupper(Uuid::uuid())
        );
    }

    public function add(Order $anOrder)
    {
        $this->_em->persist($anOrder);
    }

    public function remove(Order $anOrder)
    {
        $this->_em->remove($anOrder);
    }

    public function findById($anId)
    {
        return $this->find($anId);
    }

    public function findByReference($reference)
    {
        $qb = $this->_em->createQueryBuilder();
        $qb->select('o')
            ->from('App\Modules\Commerce\DomainModel\Order\Order', 'o')
            ->where('o.reference = :reference')
            ->setParameter('reference', $reference);
        return $qb->getQuery()->getOneOrNullResult();
    }

    public function findByIdOrFail($anId)
    {
        $record = $this->findById($anId);
        if(!$record) throw new NotFoundHttpException("Record not found.");
        return $record;
    }

    public function getDatatableData($dateFrom = null, $dateTo = null, $statuses = [])
    {
        $qb = $this->_em->createQueryBuilder();
        $qb->select("o.id, TO_CHAR(o.createdAt, 'dd.mm.YYYY, HH24:MM:SS') as createdAt, o.number, o.status.name as status, o.paymentType.payment as paymentType, charge.amount as price, charge.status as paymentStatus")
            ->addSelect("STRING_AGG(CONCAT(payment.id, '-', CAST(entries.amount as string)), '|') as payments")
            ->addSelect("dbill.name as bill, dprebill.name as prebill")
            ->from('App\Modules\Commerce\DomainModel\Order\Order', 'o')
            ->innerJoin('o.charge', 'charge')
            ->leftJoin('charge.entries', 'entries')
            ->leftJoin('entries.income', 'income')
            ->leftJoin('income.payment', 'payment')
            ->leftJoin('o.documents', 'dbill', 'WITH', 'dbill.type=?1')
            ->leftJoin('o.documents', 'dprebill', 'WITH', 'dprebill.type=?2')
            ->setParameter(1, 'bill')
            ->setParameter(2, 'prebill')
            ->orderBy('o.createdAt', 'desc')
            ->groupBy('o.id, charge.status, charge.amount, dbill.name, dprebill.name');

        if($dateFrom && $dateTo){
            $dateTo = Carbon::createFromFormat('Y-m-d', $dateTo)->addDay(1)->format('Y-m-d');
            $qb->andWhere($qb->expr()->between('o.createdAt', ':from', ':to'))
                ->setParameter('from', $dateFrom)
                ->setParameter('to', $dateTo);
        }
        if($statuses){
            $qb->andWhere($qb->expr()->in('o.status.name', ':statuses'))
                ->setParameter('statuses', $statuses);
        }

        return collect($qb->getQuery()->getScalarResult());
    }

    public function getDataTableDataForEntry()
    {
        $qb = $this->_em->createQueryBuilder();
        $qb->select("o.id, TO_CHAR(o.createdAt, 'dd.mm.YYYY, HH24:MM:SS') as createdAt, o.number, charge.amount as price, charge.status as paymentStatus")
            ->addSelect("dprebill.name as prebill")
            ->from('App\Modules\Commerce\DomainModel\Order\Order', 'o')
            ->innerJoin('o.charge', 'charge')
            ->leftJoin('o.documents', 'dprebill', 'WITH', 'dprebill.type=?1')
            ->where('charge.status != :status')
            ->setParameter(1, 'prebill')
            ->setParameter('status', Charge::STATUS_BINDED)
            ->orderBy('o.createdAt', 'desc');

        return collect($qb->getQuery()->getScalarResult());
    }



}