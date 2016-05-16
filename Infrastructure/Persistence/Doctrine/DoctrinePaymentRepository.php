<?php

namespace App\Modules\Commerce\Infrastructure\Persistence\Doctrine;


use App\Modules\Commerce\DomainModel\Balance\Income;
use App\Modules\Commerce\DomainModel\Payment\PaymentRepository;
use App\Modules\Commerce\DomainModel\Payment\Payment;
use App\Modules\Commerce\DomainModel\Payment\PaymentId;
use Carbon\Carbon;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Mapping\ClassMetadata;
use Faker\Provider\Uuid;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class DoctrinePaymentRepository extends EntityRepository implements PaymentRepository
{
    public function __construct(EntityManager $em, ClassMetadata $class ){
        parent::__construct($em, $class);
    }

    public function nextIdentity() {
        return PaymentId::create(
            strtoupper(Uuid::uuid())
        );
    }

    public function add(Payment $anPayment)
    {
        $this->_em->persist($anPayment);
    }

    public function remove(Payment $anPayment)
    {
        $this->_em->remove($anPayment);
    }

    public function findById($anId)
    {
        return $this->find($anId);
    }

    public function findByIdOrFail($anId)
    {
        $record = $this->findById($anId);
        if(!$record) throw new NotFoundHttpException("Record not found.");
        return $record;
    }

    public function findByExternalId($anExternalId)
    {
        $qb = $this->_em->createQueryBuilder();
        $qb->select('p.id')
            ->from('App\Modules\Commerce\DomainModel\Payment\Payment', 'p')
            ->where('p.externalId = :extId')
            ->setParameter('extId', $anExternalId);

        return $qb->getQuery()->getOneOrNullResult();
    }

    public function getDatatableData($dateFrom = null, $dateTo = null, $statuses = [])
    {
        $qb = $this->_em->createQueryBuilder();
        $qb->select("p.id, TO_CHAR(p.createdAt, 'dd.mm.YYYY, HH24:MM:SS') as createdAt, p.type.payment as paymentType, income.amount as price, pi.reference, pi.iban, income.status as status")
            ->addSelect("STRING_AGG(CONCAT(_order.id, '-', _order.number, '-',CAST(entries.amount as string)), '|') as orders")
            ->from('App\Modules\Commerce\DomainModel\Payment\Payment', 'p')
            ->innerJoin('p.income', 'income')
            ->leftJoin('income.entries', 'entries')
            ->leftJoin('entries.charge', 'charge')
            ->leftJoin('charge.order', '_order')
            ->leftJoin('App\Modules\Commerce\DomainModel\Payment\Iban\PaymentIban', 'pi', 'WITH', 'pi.id = p.id')
            ->orderBy('p.createdAt', 'desc')
            ->groupBy('p.id, pi.reference, pi.iban, income.status, income.amount');

        if($dateFrom && $dateTo){
            $dateTo = Carbon::createFromFormat('Y-m-d', $dateTo)->addDay(1)->format('Y-m-d');
            $qb->andWhere($qb->expr()->between('p.createdAt', ':from', ':to'))
                ->setParameter('from', $dateFrom)
                ->setParameter('to', $dateTo);
        }
        if($statuses){
            $qb->andWhere($qb->expr()->in('income.status', ':statuses'))
                ->setParameter('statuses', $statuses);
        }

        return collect($qb->getQuery()->getScalarResult());
    }

    public function getDataTableDataForEntry()
    {
        $qb = $this->_em->createQueryBuilder();
        $qb->select("p.id, TO_CHAR(p.createdAt, 'dd.mm.YYYY, HH24:MM:SS') as createdAt, p.type.payment as paymentType, income.amount as price, pi.reference, income.status as status")
            ->from('App\Modules\Commerce\DomainModel\Payment\Payment', 'p')
            ->innerJoin('p.income', 'income')
            ->leftJoin('income.entries', 'entries')
            ->leftJoin('App\Modules\Commerce\DomainModel\Payment\Iban\PaymentIban', 'pi', 'WITH', 'pi.id = p.id')
            ->where('income.status != :status')
            ->setParameter('status', Income::STATUS_BINDED)
            ->orderBy('p.createdAt', 'desc');

        return collect($qb->getQuery()->getScalarResult());
    }

    public function getDataTableDataForStatement($statementId)
    {
        $qb = $this->_em->createQueryBuilder();
        $qb->select("p.id,  p.type.payment as paymentType, p.iban, income.amount as price, p.reference, income.status as status")
            ->addSelect("TO_CHAR(p.createdAt, 'dd.mm.YYYY, HH24:MM:SS') as createdAt")
            ->from('App\Modules\Commerce\DomainModel\Payment\Iban\PaymentIban', 'p')
            ->innerJoin('p.income', 'income')
            ->leftJoin('income.entries', 'entries')
            ->where('p.statement = :statementId')
            ->setParameter('statementId', $statementId)
            ->orderBy('p.createdAt', 'desc');

        return collect($qb->getQuery()->getScalarResult());
    }

}