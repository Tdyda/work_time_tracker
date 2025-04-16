<?php

namespace App\Repository;

use App\Entity\SecuritySettings;
use App\Entity\SystemWorkSettings;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use LogicException;

/**
 * @extends ServiceEntityRepository<SystemWorkSettings>
 */
class SystemWorkSettingsRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, SystemWorkSettings::class);
    }
}
