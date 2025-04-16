<?php

namespace App\Entity;

use App\Repository\SystemWorkSettingsRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: SystemWorkSettingsRepository::class)]
class SystemWorkSettings
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private ?int $monthly_work_norm = null;

    #[ORM\Column]
    private ?float $hourly_rate = null;

    #[ORM\Column]
    private ?float $overtime_multiplier = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getMonthlyWorkNorm(): ?int
    {
        return $this->monthly_work_norm;
    }

    public function setMonthlyWorkNorm(int $monthly_work_norm): static
    {
        $this->monthly_work_norm = $monthly_work_norm;

        return $this;
    }

    public function getHourlyRate(): ?float
    {
        return $this->hourly_rate;
    }

    public function setHourlyRate(float $hourly_rate): static
    {
        $this->hourly_rate = $hourly_rate;

        return $this;
    }

    public function getOvertimeMultiplier(): ?float
    {
        return $this->overtime_multiplier;
    }

    public function setOvertimeMultiplier(float $overtime_multiplier): static
    {
        $this->overtime_multiplier = $overtime_multiplier;

        return $this;
    }
}
