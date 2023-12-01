<?php
// src/Service/UserService.php

namespace App\Service;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Security;

class UserService
{

    private $entityManager;
    private $passwordEncoder;
    private $userRepository;
    private $security;


    public function __construct(
        EntityManagerInterface $entityManager,
        UserPasswordHasherInterface $passwordEncoder,
        UserRepository $userRepository,
        Security  $security,

    ) {
        $this->entityManager = $entityManager;
        $this->passwordEncoder = $passwordEncoder;
        $this->userRepository = $userRepository;
        $this->security = $security;
    }

    public function createUser(array $userData): User
    {
        $existingUser = $this->userRepository->findOneByEmail($userData['email']);

        if ($existingUser) {
            throw new \InvalidArgumentException('User with this email already exists');
        } else {
            $user = new User();
            $user->setUsername($userData['username']);
            $user->setEmail($userData['email']);
            $hashedPassword = $this->passwordEncoder->hashPassword($user, $userData['password']);
            $user->setPassword($hashedPassword);

            $this->entityManager->persist($user);
            $this->entityManager->flush();
            return $user;
        }
    }

    public function getAllUsers(): array
    {
        $users = $this->userRepository->findAll();
        return $users;
    }

    public function getCurrenctUser()
    {
        $user = $this->security->getUser();
        return $user;
    }
}
