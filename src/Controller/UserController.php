<?php


// src/Controller/UserController.php

namespace App\Controller;

use App\Entity\User;
use App\Service\UserService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class UserController extends AbstractController
{
    private $userService;

    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }

    /**
     * @Route("/api/user/create", name="api_user_create", methods={"POST"})
     */
    public function createUser(Request $request): JsonResponse
    {
        $requestData = json_decode($request->getContent(), true);

        // Validate required fields
        $validationResult = $this->validateUserData($requestData);
        if ($validationResult !== true) {
            return $validationResult;
        }

        $user = $this->userService->createUser($requestData);

        return $this->json(['message' => 'User created successfully', 'user' => $user]);
    }

    /**
     * @Route("/api/users", name="api_users_list", methods={"GET"})
     */
    public function listUsers(): JsonResponse
    {
        $users = $this->userService->getAllUsers();

        return $this->json(['users' => $users]);
    }

    private function validateUserData(array $data)
    {
        $requiredFields = ['username', 'email', 'password'];

        foreach ($requiredFields as $field) {
            if (!isset($data[$field])) {
                return $this->json(['error' => ucfirst($field) . ' is a required field'], 400);
            }
        }

        return true; // Validation passed
    }
}
