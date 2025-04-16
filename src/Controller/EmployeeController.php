<?php

namespace App\Controller;

use App\Entity\Employee;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

final class EmployeeController extends AbstractController
{
    #[Route('/employee', name: 'create_employee', methods: ['POST'])]
    public function createEmployee(Request $request, EntityManagerInterface $em): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $firstName = $data['firstName'] ?? null;
        $lastName = $data['lastName'] ?? null;

        if (!$firstName || !$lastName) {
            return $this->json(['error' => 'Missing required fields'], 400);
        }

        $employee = new Employee();
        $employee->setFirstName($firstName);
        $employee->setLastName($lastName);
        $employee->setCreatedAt(new \DateTimeImmutable());

        $em->persist($employee);
        $em->flush();


        return $this->json([
            'response' => [
                'id' => $employee->getUuid()
            ]
        ]);
    }
}
