<?php

namespace App\Controller;

use App\Config\RouteAction;
use App\Config\RouteType;
use App\Entity\User;
use App\Response\JsonResponseBuilder;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;

#[Route(path: '/account', name: RouteType::Account->value)]
final class AccountController extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly JsonResponseBuilder $jsonResponseBuilder,
        private UserPasswordHasherInterface $passwordHasher,
    ) {
    }

    #[Route(path: '', name: RouteAction::Create->value, methods: ['POST'])]
    public function create(
        #[MapRequestPayload(
            serializationContext: ['groups' => ['user:create']],
            validationGroups: ['Default', 'user:create'],
        )]
        User $user,
    ): JsonResponse {
        /** @var string $plainPassword asserted by validator */
        $plainPassword = $user->getPlainPassword();
        $user->setPassword(
            $this->passwordHasher->hashPassword($user, $plainPassword)
        );
        $user->setPlainPassword(null);
        $this->entityManager->persist($user);
        $this->entityManager->flush();

        return $this->jsonResponseBuilder->single($user, ['user:owner']);
    }
}
