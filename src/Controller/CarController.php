<?php

namespace App\Controller;

use App\Service\CarService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\Normalizer\AbstractObjectNormalizer;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;

class CarController extends AbstractController
{
    private $carService;

    public function __construct(CarService $carService)
    {
        $this->carService = $carService;
    }

    /**
     * @Route("/api/cars", name="api_cars_list", methods={"GET"})
     */
    public function listCars(): JsonResponse
    {
        $cars = $this->carService->getAllCars();
        $cars = $this->serializedResponse($cars);

        return $this->json($cars);
    }
    /**
     * @Route("/api/cars", name="api_car_create", methods={"POST"})
     */

    public function createCar(Request $request): JsonResponse
    {

        $requestData = json_decode($request->getContent(), true);


        // Validate required fields
        $validationResult = $this->validateCarData($requestData);
        if ($validationResult !== true) {
            return $validationResult;
        }


        $this->carService->createCar($requestData);

        return $this->json(['message' => 'Car created successfully']);
    }
    /**
     * @Route("/api/car/{id}", name="find_car_by_id")
     */

    public function getCarDetails(int $id): JsonResponse
    {
        $car = $this->carService->getCarDetails($id);
        $car = $this->serializedResponse($car);
        return $this->json($car);
    }

    private function validateCarData(array $data)
    {
        $requiredFields = ['make', 'model', 'color', 'year'];

        foreach ($requiredFields as $field) {
            if (!isset($data[$field])) {
                return $this->json(['error' => ucfirst($field) . ' is a required field'], 400);
            }
        }

        return true; // Validation passed
    }


    public function serializedResponse($model): array
    {
        $encoder = new JsonEncoder();
        $defaultContext = [
            AbstractNormalizer::CIRCULAR_REFERENCE_HANDLER => function ($object, $format, $context) {
                return $object->getId();
            },
        ];
        $normalizer = new ObjectNormalizer(null, null, null, null, null, null, $defaultContext);



        $serializer = new Serializer([$normalizer], [$encoder]);
        // $rseult = $serializer->serialize($model, 'json', [
        //     'groups' => 'reservation:read',
        // ]);

        $result = $serializer->normalize($model, null, [AbstractObjectNormalizer::ENABLE_MAX_DEPTH => true]);
        return $result;
    }
}
