<?php

namespace App\Controller;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserController extends AbstractController
{
    #[Route('/api/register', name: 'api_register', methods: ['POST'])]
    public function register(
        Request $request,
        UserPasswordHasherInterface $passwordHasher,
        EntityManagerInterface $entityManager
    ): JsonResponse {
        $data = json_decode($request->getContent(), true);

        // Validación
        if (!isset($data['email'], $data['password'])) {
            return new JsonResponse(['error' => 'Faltan datos'], 400);
        }

        $user = new User();
        $user->setEmail($data['email']);
        $hashedPassword = $passwordHasher->hashPassword($user, $data['password']);
        $user->setPassword($hashedPassword);

        $entityManager->persist($user);
        $entityManager->flush();

        return new JsonResponse(['message' => 'Usuario creado'], 201);
    }

    #[Route('/api/change-password', name: 'api_change_password', methods: ['POST'])]
    public function changePassword(
        Request $request,
        UserPasswordHasherInterface $passwordHasher,
        EntityManagerInterface $entityManager
    ): JsonResponse {
        $data = json_decode($request->getContent(), true);

        // Validación: se requieren email, oldPassword y newPassword.
        if (!isset($data['email'], $data['oldPassword'], $data['newPassword'])) {
            return new JsonResponse(['error' => 'Faltan datos'], 400);
        }

        // Buscar usuario por email.
        $user = $entityManager->getRepository(User::class)->findOneBy(['email' => $data['email']]);
        if (!$user) {
            return new JsonResponse(['error' => 'Usuario no encontrado'], 404);
        }

        // Verificar que la contraseña actual es correcta.
        if (!$passwordHasher->isPasswordValid($user, $data['oldPassword'])) {
            return new JsonResponse(['error' => 'Contraseña actual incorrecta'], 400);
        }

        // Hashear y actualizar la nueva contraseña.
        $hashedPassword = $passwordHasher->hashPassword($user, $data['newPassword']);
        $user->setPassword($hashedPassword);
        $entityManager->persist($user);
        $entityManager->flush();

        return new JsonResponse(['message' => 'Contraseña actualizada exitosamente'], 200);
    }
}
