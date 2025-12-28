<?php

namespace App\Controller;

use App\Config\RouteAction;
use App\Config\RouteType;
use App\Entity\User;
use App\Response\JsonResponseBuilder;
use App\Security\Voter\UserVoter;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route(path: '/profile/{username}', name: RouteType::Profile->value)]
final class ProfileController extends AbstractController
{
    public function __construct(
        private readonly JsonResponseBuilder $jsonResponseBuilder,
    ) {
    }

    #[Route(path: '', name: RouteAction::Get->value, methods: ['GET'])]
    #[IsGranted(attribute: UserVoter::PUBLIC, subject: 'user', statusCode: Response::HTTP_NOT_FOUND)]
    public function get(
        #[MapEntity(mapping: ['username' => 'username'])] User $user,
    ): JsonResponse {
        return $this->jsonResponseBuilder->single($user, ['user:profile']);
    }
}
