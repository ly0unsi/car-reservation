<?php


// src/Controller/UserController.php

namespace App\Controller;

use App\Service\ReservationService;
use App\Service\UserService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use OpenApi\Annotations as OA;
use Nelmio\ApiDocBundle\Annotation\Model;
use Nelmio\ApiDocBundle\Annotation\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\Normalizer\AbstractObjectNormalizer;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;

class ReservationController extends AbstractController
{
    private $reservationService;
    private $userService;


    public function __construct(
        ReservationService $reservationService,
        UserService $userService
    ) {
        $this->reservationService = $reservationService;
        $this->userService = $userService;
    }



    /**
     * @Route("/api/reservations", name="api_reservation_create", methods={"POST"})
     * @OA\Response(
     *     response=200,
     *     description="Returns the rewards of an user",
     *     @OA\JsonContent(
     *        type="array",
     *        @OA\Items(ref=@Model(type=Reservation::class, groups={"full"}))
     *     )
     * )
     * @OA\Parameter(
     *     name="order",
     *     in="query",
     *     description="The field used to order rewards",
     *     @OA\Schema(type="string")
     * )
     * @OA\Tag(name="rewards")
     */

    public function createReservation(Request $request): JsonResponse
    {

        $requestData = json_decode($request->getContent(), true);

        try {
            // Call the service to create the reservation
            $this->reservationService->addReservation($requestData);

            return $this->json(['message' => 'Reservation created successfully']);
        } catch (\InvalidArgumentException $e) {
            // Return error message
            return new JsonResponse(json_decode($e->getMessage()), JsonResponse::HTTP_BAD_REQUEST);
        }
    }



    /**
     * @Route("/api/reservations/{reservationId}", name="api_reservation_edit", methods={"PUT"})
     */

    public function editReservation(Request $request, $reservationId): JsonResponse
    {

        $requestData = json_decode($request->getContent(), true);

        try {
            // Call the service to create the reservation
            $this->reservationService->editReservation($reservationId, $requestData);


            return $this->json(['message' => 'Reservation updated successfully']);
        } catch (\InvalidArgumentException $e) {
            // Return error message
            return new JsonResponse(json_decode($e->getMessage()), JsonResponse::HTTP_BAD_REQUEST);
        }
    }



    /**
     * @Route("/api/reservations/{reservationId}", name="api_reservation_delete", methods={"DELETE"})
     */
    public function deleteReservation(int $reservationId): JsonResponse
    {
        // Call the deleteReservation method from the service
        $this->reservationService->deleteReservation($reservationId);

        return new JsonResponse(['message' => 'Reservation deleted successfully'], JsonResponse::HTTP_OK);
    }


    /**
     * @Route("api/users/{userId}/reservations", name="api_reservation_list", methods={"GET"})
     */
    public function getUserReservations($userId): JsonResponse
    {

        $authUserId = $this->userService->getCurrenctUser()->getId();
        if ($authUserId != $userId) {
            return $this->json(["message" => "Hmm reservations are not urs sir"], 400);
        }

        $reservations = $this->reservationService->getuserReservations($userId);

        //to avoid circular reference
        $result = $this->serializedResponse($reservations);
        return $this->json($result);
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


        $result = $serializer->normalize($model, null, [AbstractObjectNormalizer::ENABLE_MAX_DEPTH => true]);
        return $result;
    }
}