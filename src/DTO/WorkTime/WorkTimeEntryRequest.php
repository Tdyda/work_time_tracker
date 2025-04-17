<?php

namespace App\DTO\WorkTime;

use Symfony\Component\Validator\Constraints as Assert;

class WorkTimeEntryRequest
{
    #[Assert\NotBlank]
    #[Assert\Uuid]
    public string $employee_uuid;

    #[Assert\NotBlank]
    #[Assert\DateTime(format: 'Y-m-d H:i')]
    public string $start_time;

    #[Assert\NotBlank]
    #[Assert\DateTime(format: 'Y-m-d H:i')]
    public string $end_time;
}
