<?php
namespace App\DataFixtures;

use App\Entity\SystemWorkSettings;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class SystemWorkSettingsFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $settings = new SystemWorkSettings();
        $settings->setHourlyRate(20);
        $settings->setOvertimeMultiplier(200);
        $settings->setMonthlyWorkNorm(40);

        $manager->persist($settings);
        $manager->flush();
    }
}
