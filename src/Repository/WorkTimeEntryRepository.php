<?php

namespace App\Repository;

use App\Entity\Employee;
use App\Entity\WorkTimeEntry;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<WorkTimeEntry>
 */
class WorkTimeEntryRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, WorkTimeEntry::class);
    }

    public function existsForEmployeeAndDay(Employee $employee, \DateTimeInterface $startDay): bool
    {
        return (bool)$this->createQueryBuilder('w')
            ->select('1')
            ->andWhere('w.employee = :employee')
            ->andWhere('w.startDay = :day')
            ->setParameter('employee', $employee)
            ->setParameter('day', $startDay->format('Y-m-d'))
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function findByEmployeeAndDateRange(
        Employee $employee,
        \DateTimeInterface $start,
        \DateTimeInterface $end
    ): array {
        return $this->createQueryBuilder('w')
            ->andWhere('w.employee = :employee')
            ->andWhere('w.startDay BETWEEN :start AND :end')
            ->setParameter('employee', $employee)
            ->setParameter('start', $start->format('Y-m-d'))
            ->setParameter('end', $end->format('Y-m-d'))
            ->getQuery()
            ->getResult();
    }
}
