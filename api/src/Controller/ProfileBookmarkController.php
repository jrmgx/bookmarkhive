<?php

namespace App\Controller;

use App\Config\RouteAction;
use App\Config\RouteType;
use App\Entity\User;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Attribute\MapQueryParameter;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Attribute\Route;

#[Route(path: '/api/profile/{username}/bookmarks', name: RouteType::ProfileBookmarks->value)]
final class ProfileBookmarkController extends BookmarkController
{
    #[Route(path: '', name: RouteAction::Collection->value, methods: ['GET'])]
    public function collection(
        #[MapEntity(mapping: ['username' => 'username'])] User $user,
        #[MapQueryParameter(name: 'tags')] ?string $tagQueryString = null,
        #[MapQueryParameter(name: 'q')] ?string $searchQueryString = null,
        #[MapQueryParameter(name: 'after')] ?string $afterQueryString = null,
    ): JsonResponse {
        return $this->collectionCommon(
            $user,
            $tagQueryString,
            $searchQueryString,
            $afterQueryString,
            ['bookmark:profile', 'tag:profile'],
            RouteType::ProfileBookmarks,
            ['username' => $user->username],
            onlyPublic: true
        );
    }

    #[Route(path: '/{id}', name: RouteAction::Get->value, methods: ['GET'])]
    public function get(
        #[MapEntity(mapping: ['username' => 'username'])] User $user,
        string $id,
    ): JsonResponse {
        $bookmark = $this->bookmarkRepository->findOneByOwnerAndId($user, $id, onlyPublic: true)
            ->getQuery()->getOneOrNullResult()
            ?? throw new NotFoundHttpException()
        ;

        return $this->jsonResponseBuilder->single($bookmark, ['bookmark:profile', 'tag:profile']);
    }
}
