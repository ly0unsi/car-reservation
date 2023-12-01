<?php

namespace App\Service;

use App\Entity\Reservation;
use App\Repository\CarRepository;
use App\Repository\ReservationRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class ReservationService
{
    private $entityManager;
    private $reservationRepository;
    private $carRepository;
    private $userRepository;
    private $validator;
    private $carService;

    public function __construct(
        EntityManagerInterface $entityManager,
        ReservationRepository $reservationRepository,
        ValidatorInterface $validator,
        UserRepository $userRepository,
        CarRepository $carRepository,
        CarService $carService,
    ) {
        $this->entityManager = $entityManager;
        $this->reservationRepository = $reservationRepository;
        $this->validator = $validator;
        $this->userRepository = $userRepository;
        $this->carRepository = $carRepository;
        $this->carService = $carService;
    }

    public function addReservation(array $reservationData): Reservation
    {
        $user = $this->userRepository->find($reservationData['user_id']);
        $car = $this->carRepository->find($reservationData['car_id']);

        // Map request data to Reservation model
        $reservation = new Reservation();
        $reservation->setStartDate(new \DateTime($reservationData['start_date']));
        $reservation->setEndDate(new \DateTime($reservationData['end_date']));
        $reservation->setCar($car);
        $reservation->setReserver($user);

        // Validate the reservation data
        $this->validateData($reservation);

        $isCarReserved = $this->carService->isCarAlreadyReserved(
            $car,
            new \DateTime($reservationData['start_date']),
            new \DateTime($reservationData['end_date']),
            null
        );


        if ($isCarReserved) {
            throw new \InvalidArgumentException(json_encode(['message' => "Car is already reserved during the specified period"]));
        } else {
            $this->entityManager->persist($reservation);
            $this->entityManager->flush();
            return $reservation;
        }
    }
    public function editReservation(int $reservationId, array $reservationData): Reservation
    {
        // Find the existing reservation
        $reservation = $this->reservationRepository->find($reservationId);

        if (!$reservation) {
            throw new \InvalidArgumentException(json_encode(['errors' => 'Reservation not found']));
        }

        // Update reservation data
        $user = $this->userRepository->find($reservationData['user_id']);
        $car = $this->carRepository->find($reservationData['car_id']);

        $reservation->setStartDate(new \DateTime($reservationData['start_date']));
        $reservation->setEndDate(new \DateTime($reservationData['end_date']));
        $reservation->setCar($car);
        $reservation->setReserver($user);

        // Validate the updated reservation data
        $this->validateData($reservation);

        // Check if the car is already reserved during the specified period
        $isCarReserved = $this->carService->isCarAlreadyReserved(
            $car,
            new \DateTime($reservationData['start_date']),
            new \DateTime($reservationData['end_date']),
            $reservationId // Exclude the current reservation from the check
        );

        if ($isCarReserved) {
            throw new \InvalidArgumentException(json_encode(['errors' => 'Car is already reserved during the specified period']));
        } else {
            // Persist the changes
            $this->entityManager->flush();
            return $reservation;
        }
    }

    public function deleteReservation(int $reservationId): void
    {
        // Find the reservation by ID
        $reservation = $this->reservationRepository->find($reservationId);

        // Check if the reservation exists
        if (!$reservation) {
            throw new \InvalidArgumentException(json_encode(['message' => 'Reservation not found']));
        }

        // Remove the reservation
        $this->entityManager->remove($reservation);
        $this->entityManager->flush();
    }

    public function getuserReservations($userId): array
    {
        $reservations = $this->reservationRepository->findReservationsByUser($userId);

        return $reservations;
    }

    private function validateData(Reservation $reservation): void
    {
        // Validate the reservation data
        $errors = $this->validator->validate($reservation);

        if (count($errors) > 0) {
            // Handle validation errors, e.g., throw an exception with JSON response
            $errorMessages = [];
            foreach ($errors as $error) {
                $errorMessages[$error->getPropertyPath()] = $error->getMessage();
            }
            if (isset($errorMessages['endDate'])) {
                throw new \InvalidArgumentException(json_encode(['message' => $errorMessages['endDate']]));
            } else {
                throw new \InvalidArgumentException(json_encode(['errors' => $errorMessages]));
            }
        }
    }
}