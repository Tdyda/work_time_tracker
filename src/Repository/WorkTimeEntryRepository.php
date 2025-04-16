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
}
