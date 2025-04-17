<?php

namespace App\Tests\Service;

use App\DTO\WorkTime\WorkTimeEntryRequest;
use App\Entity\Employee;
use App\Entity\WorkTimeEntry;
use App\Repository\EmployeeRepository;
use App\Repository\WorkTimeEntryRepository;
use App\Service\WorkTime\WorkTimeService;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;

class WorkTimeServiceTest extends TestCase
{
    public function testSuccessfulWorkTimeRegistration(): void
    {
        $dto = new WorkTimeEntryRequest();
        $dto->employee_uuid = '123e4567-e89b-12d3-a456-426614174000';
        $dto->start_time = '2025-04-20 08:00';
        $dto->end_time = '2025-04-20 14:00';

        $employee = $this->createMock(Employee::class);

        $employeeRepo = $this->createMock(EmployeeRepository::class);
        $employeeRepo->method('find')->with($dto->employee_uuid)->willReturn($employee);

        $entryRepo = $this->createMock(WorkTimeEntryRepository::class);
        $entryRepo->method('existsForEmployeeAndDay')->willReturn(false);

        $em = $this->createMock(EntityManagerInterface::class);
        $em->expects($this->once())->method('persist')->with($this->isInstanceOf(WorkTimeEntry::class));
        $em->expects($this->once())->method('flush');

        $service = new WorkTimeService($employeeRepo, $entryRepo, $em);

        $service->register($dto);

        $this->assertTrue(true);
    }

    public function testThrowsExceptionWhenEmployeeNotFound(): void
    {
        $dto = new WorkTimeEntryRequest();
        $dto->employee_uuid = 'non-existent-uuid';
        $dto->start_time = '2025-04-20 08:00';
        $dto->end_time = '2025-04-20 14:00';

        $employeeRepo = $this->createMock(EmployeeRepository::class);
        $employeeRepo->method('find')->willReturn(null);

        $entryRepo = $this->createMock(WorkTimeEntryRepository::class);
        $em = $this->createMock(EntityManagerInterface::class);

        $service = new WorkTimeService($employeeRepo, $entryRepo, $em);

        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('Employee not found.');

        $service->register($dto);
    }
}
