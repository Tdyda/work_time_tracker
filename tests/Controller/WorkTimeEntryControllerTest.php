<?php

namespace App\Tests\Controller;

use App\Entity\SystemWorkSettings;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class WorkTimeEntryControllerTest extends WebTestCase

{
    public function testSuccessfulWorkTimeEntry(): void
    {
        $client = static::createClient();
        $container = $client->getContainer();
        $em = $container->get(EntityManagerInterface::class);

        $employee = new \App\Entity\Employee();
        $employee->setFirstName('Auto');
        $employee->setLastName('Generated');
        $employee->setCreatedAt(new \DateTimeImmutable());
        $em->persist($employee);
        $em->flush();

        $client->request(
            'POST',
            '/work-time',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode([
                'employee_uuid' => $employee->getUuid(),
                'start_time' => '2025-04-20 08:00',
                'end_time' => '2025-04-20 14:00',
            ])
        );

        $this->assertResponseIsSuccessful();
        $this->assertResponseStatusCodeSame(200);

        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('message', $data);
        $this->assertEquals('Czas pracy został dodany!', $data['message']);
    }


    public function testInvalidDateFormatReturnsBadRequest(): void
    {
        $client = static::createClient();
        $container = $client->getContainer();
        $em = $container->get(EntityManagerInterface::class);

        $employee = new \App\Entity\Employee();
        $employee->setFirstName('Auto');
        $employee->setLastName('InvalidFormat');
        $employee->setCreatedAt(new \DateTimeImmutable());
        $em->persist($employee);
        $em->flush();

        $client->request(
            'POST',
            '/work-time',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode([
                'employee_uuid' => $employee->getUuid(),
                'start_time' => '20-04-2025 08:00', // ⛔️ zły format
                'end_time' => '2025-04-20 14:00',
            ])
        );

        $this->assertResponseStatusCodeSame(400);

        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('errors', $data);
        $this->assertStringContainsString('start_time', $data['errors']);
    }


    public function testWorkTimeExceeds12HoursReturnsBadRequest(): void
    {
        $client = static::createClient();
        $container = $client->getContainer();
        $em = $container->get(EntityManagerInterface::class);

        $employee = new \App\Entity\Employee();
        $employee->setFirstName('Auto');
        $employee->setLastName('TooMuchWork');
        $employee->setCreatedAt(new \DateTimeImmutable());
        $em->persist($employee);
        $em->flush();

        $client->request(
            'POST',
            '/work-time',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode([
                'employee_uuid' => $employee->getUuid(),
                'start_time' => '2025-04-22 08:00',
                'end_time' => '2025-04-22 21:00', // ⛔️ 13 godzin
            ])
        );

        $this->assertResponseStatusCodeSame(400);

        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('error', $data);
        $this->assertStringContainsString('cannot exceed 12 hours', $data['error']);
    }


    public function testMissingEndTimeReturnsBadRequest(): void
    {
        $client = static::createClient();
        $container = $client->getContainer();
        $em = $container->get(EntityManagerInterface::class);

        $employee = new \App\Entity\Employee();
        $employee->setFirstName('Auto');
        $employee->setLastName('MissingEnd');
        $employee->setCreatedAt(new \DateTimeImmutable());
        $em->persist($employee);
        $em->flush();

        $client->request(
            'POST',
            '/work-time',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode([
                'employee_uuid' => $employee->getUuid(),
                'start_time' => '2025-04-23 08:00',
                // brak end_time
            ])
        );

        $this->assertResponseStatusCodeSame(400);

        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('errors', $data);
        $this->assertStringContainsString('end_time', $data['errors']);
    }


    public function testMissingEmployeeUuidReturnsBadRequest(): void
    {
        $client = static::createClient();

        $client->request(
            'POST',
            '/work-time',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode([
                // ⛔️ brak employee_uuid
                'start_time' => '2025-04-24 08:00',
                'end_time' => '2025-04-24 14:00',
            ])
        );

        $this->assertResponseStatusCodeSame(400);

        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('errors', $data);
        $this->assertStringContainsString('employee_uuid', $data['errors']);
    }

    public function testSuccessfulSummaryForSingleDay(): void
    {
        $client = static::createClient();
        $container = $client->getContainer();

        // Dodaj testowego pracownika
        $em = $container->get(EntityManagerInterface::class);
        $employee = new \App\Entity\Employee();
        $employee->setFirstName('Test');
        $employee->setLastName('User');
        $employee->setCreatedAt(new \DateTimeImmutable());
        $em->persist($employee);

        // Dodaj wpis czasu pracy
        $entry = new \App\Entity\WorkTimeEntry();
        $entry->setEmployee($employee);
        $entry->setStartTime(new \DateTimeImmutable('2025-04-20 08:00'));
        $entry->setEndTime(new \DateTimeImmutable('2025-04-20 14:00'));
        $entry->setStartDay(new \DateTime('2025-04-20'));
        $em->persist($entry);

        $settings = $em->getRepository(SystemWorkSettings::class)->findFirst();

        if (!$settings) {
            $settings = new \App\Entity\SystemWorkSettings();
            $settings->setHourlyRate(20);
            $settings->setOvertimeMultiplier(200);
            $settings->setMonthlyWorkNorm(40);
            $em->persist($settings);
        }

        $em->flush();

        // Wysyłamy request
        $client->request(
            'GET',
            '/summary',
            [
                'employee_uuid' => $employee->getUuid(),
                'date' => '2025-04-20',
            ]
        );

        $this->assertResponseIsSuccessful();

        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('response', $data);
        $this->assertArrayHasKey('normal_hours', $data['response']);
        $this->assertEquals(6, $data['response']['normal_hours']);
    }

    public function testSummaryReturnsBadRequestOnInvalidDateFormat(): void
    {
        $client = static::createClient();

        $client->request('GET', '/summary', [
            'employee_uuid' => '2fcf0770-32d1-410d-8e5d-5959f730e090',
            'date' => '2025/04', // ⛔️ zły format
        ]);

        $this->assertResponseStatusCodeSame(400);

        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('errors', $data);
        $this->assertStringContainsString('date', $data['errors']);
    }

    public function testSummaryReturns404WhenEmployeeNotFound(): void
    {
        $client = static::createClient();

        $client->request(
            'GET',
            '/summary',
            [
                'employee_uuid' => '2fcf0770-32d1-410d-8e5d-5959f730e099',
                'date' => '2025-04-20',
            ],
            [],
            ['HTTP_ACCEPT' => 'application/json']
        );


        $this->assertResponseStatusCodeSame(404);

        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertNotNull($data);

        $this->assertArrayHasKey('detail', $data);
        $this->assertStringContainsString('not found', strtolower($data['detail']));
    }

    public function testSummaryWithOvertimeForMonth(): void
    {
        $client = static::createClient();
        $container = $client->getContainer();
        $em = $container->get(EntityManagerInterface::class);

        // Utwórz pracownika
        $employee = new \App\Entity\Employee();
        $employee->setFirstName('Test');
        $employee->setLastName('Overtime');
        $employee->setCreatedAt(new \DateTimeImmutable());
        $em->persist($employee);


        $settings = $em->getRepository(SystemWorkSettings::class)->findFirst();
        if (!$settings) {
            // Utwórz domyślne ustawienia
            $settings = new \App\Entity\SystemWorkSettings();
            $settings->setHourlyRate(20);
            $settings->setOvertimeMultiplier(200);
            $settings->setMonthlyWorkNorm(40);
            $em->persist($settings);
        }

        $em->flush();

        // Dodaj 8 wpisów po 6h = 48h
        for ($i = 1; $i <= 8; $i++) {
            $entry = new \App\Entity\WorkTimeEntry();
            $entry->setEmployee($employee);
            $entry->setStartTime(new \DateTimeImmutable("2025-04-$i 08:00"));
            $entry->setEndTime(new \DateTimeImmutable("2025-04-$i 14:00"));
            $entry->setStartDay(new \DateTime("2025-04-$i"));
            $em->persist($entry);
        }

        $em->flush();

        // Wywołaj endpoint
        $client->request('GET', '/summary', [
            'employee_uuid' => $employee->getUuid(),
            'date' => '2025-04',
        ], [], ['HTTP_ACCEPT' => 'application/json']);

        $this->assertResponseIsSuccessful();

        $data = json_decode($client->getResponse()->getContent(), true);
        $response = $data['response'];

        $this->assertEquals(40, $response['normal_hours']);
        $this->assertEquals(20, $response['normal_rate']);
        $this->assertEquals(8, $response['overtime_hours']);
        $this->assertEquals(40, $response['overtime_rate']);
        $this->assertEquals('1120 PLN', $response['total_payment']);
    }

    public function testSummaryWhenNoEntriesExist(): void
    {
        $client = static::createClient();
        $container = $client->getContainer();
        $em = $container->get(EntityManagerInterface::class);

        // Dodaj pracownika
        $employee = new \App\Entity\Employee();
        $employee->setFirstName('No');
        $employee->setLastName('Entries');
        $employee->setCreatedAt(new \DateTimeImmutable());
        $em->persist($employee);

        $settings = $em->getRepository(SystemWorkSettings::class)->findFirst();
        if (!$settings) {
            // Utwórz domyślne ustawienia
            $settings = new \App\Entity\SystemWorkSettings();
            $settings->setHourlyRate(20);
            $settings->setOvertimeMultiplier(200);
            $settings->setMonthlyWorkNorm(40);
            $em->persist($settings);
        }

        $em->flush();

        // Wywołanie endpointa
        $client->request('GET', '/summary', [
            'employee_uuid' => $employee->getUuid(),
            'date' => '2025-04',
        ], [], ['HTTP_ACCEPT' => 'application/json']);

        $this->assertResponseIsSuccessful();

        $data = json_decode($client->getResponse()->getContent(), true);
        $response = $data['response'];

        $this->assertEquals(0, $response['normal_hours']);
        $this->assertEquals(0, $response['overtime_hours']);
        $this->assertEquals(20, $response['normal_rate']);
        $this->assertEquals(40, $response['overtime_rate']);
        $this->assertEquals('0 PLN', $response['total_payment']);
    }


    protected static function getKernelClass(): string
    {
        return \App\Kernel::class;
    }
}
