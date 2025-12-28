<?php

namespace App\Controller;

use App\Config\RouteAction;
use App\Config\RouteType;
use App\Entity\User;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Attribute\Route;

#[Route(path: '/profile/{username}/tags', name: RouteType::ProfileTags->value)]
final class ProfileTagController extends TagController
{
    #[Route(path: '', name: RouteAction::Collection->value, methods: ['GET'])]
    public function collection(
        #[MapEntity(mapping: ['username' => 'username'])] User $user,
    ): JsonResponse {
        return $this->collectionCommon($user, ['tag:profile'], onlyPublic: true);
    }

    #[Route(path: '/{slug}', name: RouteAction::Get->value, methods: ['GET'])]
    public function get(
        #[MapEntity(mapping: ['username' => 'username'])] User $user,
        string $slug,
    ): JsonResponse {
        $tag = $this->tagRepository->findOneByOwnerAndSlug($user, $slug, onlyPublic: true)
            ->getQuery()->getOneOrNullResult()
            ?? throw new NotFoundHttpException()
        ;

        return $this->jsonResponseBuilder->single($tag, ['tag:profile']);
    }
}
