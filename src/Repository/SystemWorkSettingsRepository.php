<?php

namespace App\Repository;

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

    public function getSingletonSettings(): SystemWorkSettings
    {
        $settings = $this->findFirst();
        if (!$settings) {
            throw new LogicException('Global security settings not found.');
        }

        return $settings;
    }

    public function findFirst(): ?SystemWorkSettings
    {
        return $this->createQueryBuilder('s')
            ->orderBy('s.id', 'ASC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
