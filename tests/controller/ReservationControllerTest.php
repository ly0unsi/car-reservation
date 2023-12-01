<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;

class ReservationControllerTest extends WebTestCase
{
    private  $token = "eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9.eyJpYXQiOjE3MDEzODY1NDYsImV4cCI6MTcwMTM5MDE0Niwicm9sZXMiOlsiUk9MRV9VU0VSIl0sImVtYWlsIjoiYW53YXJAZ21haWwuY29tIn0.dPsnqllEGHkP1sc3dAAzptk5ruTGH1J73NAYw3Q8fHcwqWmo5grXBskRN4Cxng8gcnbZidZyInEkhOqWhKMJRuYdy7r0cmE8yfIalT3y3tfJS8BJFDT7iuntmGG8BrUtsAUA-X0TWPMj8AMI3x-hLGXGP1qmCG4XIyh77laA4SxFcogHYt2pLIW6ZZ8xXzGeVMH8PBQimAlxJfpvCIOZTbxBaPmtsh1W1xsPhVWteicB-wavbvh9eZQfLtXTrw0T5RBRp5uB-JnjCr8mRU0lHdrK0mKL7gkixxYb_TWMLz9rpGyTywxxreBOR1RsGsbCFL6aounhhh4-gBEYGjXaWQ";
    public function testCreateExistedReservation(): void
    {
        $client = static::createClient();

        // Sample request data
        $requestData = [
            'user_id' => "1",
            'car_id' => "1",
            'start_date' => '2023-08-06 12:00:00',
            'end_date' => '2023-08-07 12:00:00',
        ];

        $client->setServerParameter('HTTP_AUTHORIZATION', 'Bearer ' . $this->token);
        // var_dump(json_encode($requestData));
        $client->request(
            'POST',
            '/api/reservations',
            [],
            [],
            [
                'CONTENT_TYPE' => 'application/json'

            ],
            json_encode($requestData)
        );

        $response = $client->getResponse();
        $responseData = json_decode($response->getContent(), true);
        $this->assertEquals('Car is already reserved during the specified period', $responseData['message']);
    }
    public function testCreateReservationWithInvalidDates(): void
    {
        $client = static::createClient();

        // Sample request data
        $requestData = [
            'user_id' => "1",
            'car_id' => "1",
            'start_date' => '2023-08-06 12:00:00',
            'end_date' => '2023-08-02 12:00:00',
        ];

        $client->setServerParameter('HTTP_AUTHORIZATION', 'Bearer ' . $this->token);

        $client->request(
            'POST',
            '/api/reservations',
            [],
            [],
            [
                'CONTENT_TYPE' => 'application/json'

            ],
            json_encode($requestData)
        );

        $response = $client->getResponse();
        $responseData = json_decode($response->getContent(), true);
        $this->assertEquals('End date must be greater than or equal to start date.', $responseData['message']);
    }
    public function testNotOwnReservations(): void
    {
        $client = static::createClient();

        // Sample request data


        $client->setServerParameter('HTTP_AUTHORIZATION', 'Bearer ' . $this->token);
        // var_dump(json_encode($requestData));
        $client->request(
            'GET',
            '/api/users/1/reservations',
            [],
            [],
            [
                'CONTENT_TYPE' => 'application/json'

            ],
            null
        );

        $response = $client->getResponse();

        $responseData = json_decode($response->getContent(), true);
        $this->assertEquals('Hmm reservations are not urs sir', $responseData['message']);
    }
}