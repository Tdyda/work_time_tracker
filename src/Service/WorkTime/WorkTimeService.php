<?php

namespace App\Service\WorkTime;

use App\DTO\WorkTime\WorkTimeEntryRequest;
use App\Entity\WorkTimeEntry;
use App\Repository\EmployeeRepository;
use App\Repository\WorkTimeEntryRepository;
use Doctrine\ORM\EntityManagerInterface;
use LogicException;

class WorkTimeService
{
    public function __construct(
        private readonly EmployeeRepository $employeeRepository,
        private readonly WorkTimeEntryRepository $workTimeEntryRepository,
        private readonly EntityManagerInterface $em
    ) {
    }

    public function register(WorkTimeEntryRequest $dto): void
    {
        $employee = $this->employeeRepository->find($dto->employee_uuid);
        if (!$employee) {
            throw new LogicException('Employee not found.');
        }

        try {
            $startTime = new \DateTimeImmutable($dto->start_time);
            $endTime = new \DateTimeImmutable($dto->end_time);
        } catch (\Exception) {
            throw new LogicException('Invalid date format.');
        }

        if ($endTime <= $startTime) {
            throw new LogicException('End time must be after start time.');
        }

        $seconds = $endTime->getTimestamp() - $startTime->getTimestamp();
        $hours = round($seconds / 3600, 2);
        if ($hours > 12) {
            throw new LogicException('Work time cannot exceed 12 hours.');
        }

        $startDay = $startTime->setTime(0, 0);

        if ($this->workTimeEntryRepository->existsForEmployeeAndDay($employee, $startDay)) {
            throw new LogicException('Work time for this day already exists.');
        }

        $entry = new WorkTimeEntry();
        $entry->setEmployee($employee);
        $entry->setStartTime($startTime);
        $entry->setEndTime($endTime);
        $entry->setStartDay($startDay);

        $this->em->persist($entry);
        $this->em->flush();
    }
}
