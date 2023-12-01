<?php

namespace App\Service;

use App\Entity\Car;
use App\Repository\CarRepository;
use App\Repository\ReservationRepository;
use Doctrine\ORM\EntityManagerInterface;

class CarService
{
    private $entityManager;
    private $carRepository;
    private $reservationRepository;

    public function __construct(
        EntityManagerInterface $entityManager,
        CarRepository $carRepository,
        ReservationRepository $reservationRepository,
    ) {
        $this->entityManager = $entityManager;
        $this->carRepository = $carRepository;
        $this->reservationRepository = $reservationRepository;
    }


    public function getAllCars(): array
    {
        $cars = $this->carRepository->findAll();
        return $cars;
    }


    public function getCarDetails($carId): car
    {
        $car = $this->carRepository->find($carId);
        return $car;
    }



    public function createCar(array $carData): Car
    {
        $car = new Car();
        $car->setMake($carData['make']);
        $car->setModel($carData['model']);
        $car->setColor($carData['color']);
        $car->setYear($carData['year']);

        $this->entityManager->persist($car);
        $this->entityManager->flush();

        return $car;
    }

    public function isCarAlreadyReserved(Car $car, \DateTimeInterface $startDate, \DateTimeInterface $endDate, $excludeReservationId): bool
    {

        $queryBuilder = $this->reservationRepository->createQueryBuilder('r')
            ->andWhere('r.car = :car')
            ->andWhere('r.startDate < :endDate AND r.endDate > :startDate')
            ->setParameter('car', $car)
            ->setParameter('startDate', $startDate)
            ->setParameter('endDate', $endDate);

        if ($excludeReservationId !== null) {
            $queryBuilder
                ->andWhere('r.id != :excludeReservationId')
                ->setParameter('excludeReservationId', $excludeReservationId);
        }

        $existingReservations = $queryBuilder
            ->getQuery()
            ->getResult();

        return count($existingReservations) > 0;
    }
}
