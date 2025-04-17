<?php

namespace App\Service\WorkTime;

use App\DTO\WorkTime\WorkTimeSummaryRequest;
use App\Repository\EmployeeRepository;
use App\Repository\SystemWorkSettingsRepository;
use App\Repository\WorkTimeEntryRepository;

class WorkTimeSummaryService
{
    public function __construct(
        private readonly EmployeeRepository $employeeRepository,
        private readonly WorkTimeEntryRepository $workRepo,
        private readonly SystemWorkSettingsRepository $settingsRepo
    ) {
    }

    /**
     * @return array{
     *     normal_hours: float,
     *     normal_rate: float,
     *     overtime_hours: float,
     *     overtime_rate: float,
     *     total_payment: string
     * }
     */
    public function calculateSummary(WorkTimeSummaryRequest $dto): array
    {
        $employee = $this->employeeRepository->findOrFail($dto->employee_uuid);

        try {
            if (preg_match('/^\d{4}-\d{2}$/', $dto->date)) {
                $start = new \DateTimeImmutable($dto->date . '-01');
                $end = $start->modify('last day of this month')->setTime(23, 59, 59);
            } else {
                $start = new \DateTimeImmutable($dto->date);
                $end = $start->setTime(23, 59, 59);
            }
        } catch (\Exception) {
            throw new \LogicException('Invalid date.');
        }

        $entries = $this->workRepo->findByEmployeeAndDateRange($employee, $start, $end);

        $totalMinutes = 0;
        foreach ($entries as $entry) {
            $startTs = $entry->getStartTime()->getTimestamp();
            $endTs = $entry->getEndTime()->getTimestamp();
            $minutes = ceil(($endTs - $startTs) / 60 / 30) * 30;
            $totalMinutes += $minutes;
        }

        $totalHours = $totalMinutes / 60;
        $roundedHours = round($totalHours, 2);

        $settings = $this->settingsRepo->getSingletonSettings();

        $normalRate = $settings->getHourlyRate();
        $overtimeRate = $normalRate * $settings->getOvertimeMultiplier() / 100;
        $monthlyNorm = $settings->getMonthlyWorkNorm();

        $normalHours = min($roundedHours, $monthlyNorm);
        $overtimeHours = max($roundedHours - $monthlyNorm, 0);

        $normalPay = $normalHours * $normalRate;
        $overtimePay = $overtimeHours * $overtimeRate;
        $totalPay = $normalPay + $overtimePay;

        return [
            'normal_hours' => $normalHours,
            'normal_rate' => $normalRate,
            'overtime_hours' => $overtimeHours,
            'overtime_rate' => $overtimeRate,
            'total_payment' => $totalPay . ' PLN',
        ];
    }
}
