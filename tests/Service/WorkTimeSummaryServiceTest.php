<?php

namespace App\Tests\Service;

use App\DTO\WorkTime\WorkTimeSummaryRequest;
use App\Entity\Employee;
use App\Entity\SystemWorkSettings;
use App\Entity\WorkTimeEntry;
use App\Repository\EmployeeRepository;
use App\Repository\SystemWorkSettingsRepository;
use App\Repository\WorkTimeEntryRepository;
use App\Service\WorkTime\WorkTimeSummaryService;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\Validation;

class WorkTimeSummaryServiceTest extends TestCase
{
    public function testSummaryForSingleDayWithoutOvertime(): void
    {
        $dto = new WorkTimeSummaryRequest(
            employee_uuid: 'uuid-123',
            date: '2025-04-20' // single day
        );

        $employee = $this->createMock(Employee::class);

        $entry = new WorkTimeEntry();
        $entry->setStartTime(new \DateTimeImmutable('2025-04-20 08:00'));
        $entry->setEndTime(new \DateTimeImmutable('2025-04-20 14:00'));
        $entry->setStartDay(new \DateTime('2025-04-20'));

        $settings = new SystemWorkSettings();
        $settings->setHourlyRate(20);
        $settings->setMonthlyWorkNorm(40);
        $settings->setOvertimeMultiplier(200);

        // Mocks
        $employeeRepo = $this->createMock(EmployeeRepository::class);
        $employeeRepo->method('findOrFail')->willReturn($employee);

        $entryRepo = $this->createMock(WorkTimeEntryRepository::class);
        $entryRepo->method('findByEmployeeAndDateRange')->willReturn([$entry]);

        $settingsRepo = $this->createMock(SystemWorkSettingsRepository::class);
        $settingsRepo->method('getSingletonSettings')->willReturn($settings);

        $service = new WorkTimeSummaryService($employeeRepo, $entryRepo, $settingsRepo);

        $result = $service->calculateSummary($dto);

        $this->assertEquals(6, $result['normal_hours']);
        $this->assertEquals(0, $result['overtime_hours']);
        $this->assertEquals(20, $result['normal_rate']);
        $this->assertEquals(40, $result['overtime_rate']);
        $this->assertEquals('120 PLN', $result['total_payment']);
    }

    public function testSummaryForFullMonthWithOvertime(): void
    {
        $dto = new WorkTimeSummaryRequest(
            employee_uuid: 'uuid-123',
            date: '2025-04'
        );

        $employee = $this->createMock(Employee::class);

        // Trzy dni po 15h (zaokrąglone): 45h
        $entries = [];

        foreach ([1, 2, 3] as $day) {
            $entry = new WorkTimeEntry();
            $entry->setStartTime(new \DateTimeImmutable("2025-04-0{$day} 08:00"));
            $entry->setEndTime(new \DateTimeImmutable("2025-04-0{$day} 23:00")); // 15h
            $entry->setStartDay(new \DateTime("2025-04-0{$day}"));
            $entries[] = $entry;
        }

        $settings = new SystemWorkSettings();
        $settings->setHourlyRate(20);
        $settings->setMonthlyWorkNorm(40);
        $settings->setOvertimeMultiplier(200);

        $employeeRepo = $this->createMock(EmployeeRepository::class);
        $employeeRepo->method('findOrFail')->willReturn($employee);

        $entryRepo = $this->createMock(WorkTimeEntryRepository::class);
        $entryRepo->method('findByEmployeeAndDateRange')->willReturn($entries);

        $settingsRepo = $this->createMock(SystemWorkSettingsRepository::class);
        $settingsRepo->method('getSingletonSettings')->willReturn($settings);

        $service = new WorkTimeSummaryService($employeeRepo, $entryRepo, $settingsRepo);

        $result = $service->calculateSummary($dto);

        $this->assertEquals(40, $result['normal_hours']);
        $this->assertEquals(5, $result['overtime_hours']);
        $this->assertEquals(20, $result['normal_rate']);
        $this->assertEquals(40, $result['overtime_rate']);
        $this->assertEquals('1000 PLN', $result['total_payment']);
    }

    public function testValidationRejectsInvalidDateFormat(): void
    {
        $dto = new WorkTimeSummaryRequest(
            employee_uuid: 'uuid-123',
            date: '2025/04' // invalid format
        );

        // Inicjalizujemy walidator z atrybutami
        $validator = Validation::createValidatorBuilder()
            ->enableAttributeMapping()
            ->getValidator();

        $errors = $validator->validate($dto);

        $this->assertGreaterThan(0, count($errors));
        $this->assertStringContainsString(
            'Date must be in format YYYY-MM or YYYY-MM-DD',
            (string)$errors
        );
    }

    public function testThrowsExceptionWhenEmployeeNotFound(): void
    {
        $dto = new WorkTimeSummaryRequest(
            employee_uuid: 'non-existent-uuid',
            date: '2025-04-20'
        );

        // Repozytorium rzuca wyjątek
        $employeeRepo = $this->createMock(EmployeeRepository::class);
        $employeeRepo->method('findOrFail')
            ->willThrowException(new \LogicException('Employee not found.'));

        $entryRepo = $this->createMock(WorkTimeEntryRepository::class);
        $settingsRepo = $this->createMock(SystemWorkSettingsRepository::class);

        $service = new WorkTimeSummaryService($employeeRepo, $entryRepo, $settingsRepo);

        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('Employee not found.');

        $service->calculateSummary($dto);
    }

    public function testSummaryWithNoWorkEntriesReturnsZeroes(): void
    {
        $dto = new WorkTimeSummaryRequest(
            employee_uuid: 'uuid-123',
            date: '2025-04'
        );

        $employee = $this->createMock(Employee::class);

        $settings = new SystemWorkSettings();
        $settings->setHourlyRate(20);
        $settings->setMonthlyWorkNorm(40);
        $settings->setOvertimeMultiplier(200);

        $employeeRepo = $this->createMock(EmployeeRepository::class);
        $employeeRepo->method('findOrFail')->willReturn($employee);

        $entryRepo = $this->createMock(WorkTimeEntryRepository::class);
        $entryRepo->method('findByEmployeeAndDateRange')->willReturn([]); // zero wpisów

        $settingsRepo = $this->createMock(SystemWorkSettingsRepository::class);
        $settingsRepo->method('getSingletonSettings')->willReturn($settings);

        $service = new WorkTimeSummaryService($employeeRepo, $entryRepo, $settingsRepo);

        $result = $service->calculateSummary($dto);

        $this->assertEquals(0, $result['normal_hours']);
        $this->assertEquals(0, $result['overtime_hours']);
        $this->assertEquals('0 PLN', $result['total_payment']);
    }
}
