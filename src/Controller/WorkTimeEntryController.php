<?php

namespace App\Controller;

use App\DTO\WorkTime\WorkTimeEntryRequest;
use App\DTO\WorkTime\WorkTimeSummaryRequest;
use App\Service\WorkTime\WorkTimeService;
use App\Service\WorkTime\WorkTimeSummaryService;
use LogicException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

final class WorkTimeEntryController extends AbstractController
{
    #[Route('/work-time', name: 'work_time_register', methods: ['POST'])]
    public function register(
        Request $request,
        SerializerInterface $serializer,
        ValidatorInterface $validator,
        WorkTimeService $workTimeService,
    ): JsonResponse {
        $dto = $serializer->deserialize($request->getContent(), WorkTimeEntryRequest::class, 'json');

        $errors = $validator->validate($dto);
        if (count($errors) > 0) {
            return $this->json(['errors' => (string)$errors], 400);
        }

        try {
            $workTimeService->register($dto);
            return $this->json(['message' => 'Czas pracy został dodany!']);
        } catch (\LogicException $e) {
            return $this->json(['error' => $e->getMessage()], 400);
        }
    }

    #[Route('/summary', name: 'work_time_summary', methods: ['GET'])]
    public function summary(
        Request $request,
        SerializerInterface $serializer,
        ValidatorInterface $validator,
        WorkTimeSummaryService $summaryService,
    ): JsonResponse {
        try {
            $dto = $serializer->deserialize(json_encode($request->query->all()), WorkTimeSummaryRequest::class, 'json');
        } catch (\Throwable $e) {
            return $this->json(['error' => 'Invalid input format: ' . $e->getMessage()], 400);
        }

        $errors = $validator->validate($dto);
        if (count($errors) > 0) {
            return $this->json(['errors' => (string)$errors], 400);
        }

        try {
            $result = $summaryService->calculateSummary($dto);
            return $this->json(['response' => $result]);
        } catch (LogicException $e) {
            return $this->json(['error' => $e->getMessage()], 400);
        }
    }
}
