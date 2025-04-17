<?php

namespace App\DTO\WorkTime;

use Symfony\Component\Validator\Constraints as Assert;

class WorkTimeSummaryRequest
{
    #[Assert\NotBlank]
    #[Assert\Uuid]
    public readonly string $employee_uuid;

    #[Assert\NotBlank]
    #[Assert\Regex(
        pattern: '/^\d{4}-\d{2}$|^\d{4}-\d{2}-\d{2}$/',
        message: 'Date must be in format YYYY-MM or YYYY-MM-DD'
    )]
    public readonly string $date;

    public function __construct(string $employee_uuid, string $date)
    {
        $this->employee_uuid = $employee_uuid;
        $this->date = $date;
    }
}
