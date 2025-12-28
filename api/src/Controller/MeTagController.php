<?php

namespace App\Controller;

use App\Config\RouteAction;
use App\Config\RouteType;
use App\Entity\Tag;
use App\Entity\User;
use App\Security\Voter\TagVoter;
use Doctrine\ORM\Exception\ORMException;
use Doctrine\ORM\ORMInvalidArgumentException;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route(path: '/users/me/tags', name: RouteType::MeTags->value)]
final class MeTagController extends TagController
{
    #[Route(path: '', name: RouteAction::Collection->value, methods: ['GET'])]
    public function collection(
        #[CurrentUser] User $user,
    ): JsonResponse {
        return $this->collectionCommon($user, ['tag:owner'], onlyPublic: false);
    }

    #[Route(path: '/{slug}', name: RouteAction::Get->value, methods: ['GET'])]
    #[IsGranted(attribute: TagVoter::OWNER, subject: 'tag', statusCode: Response::HTTP_NOT_FOUND)]
    public function get(
        #[MapEntity(mapping: ['slug' => 'slug'])] Tag $tag,
    ): JsonResponse {
        return $this->jsonResponseBuilder->single($tag, ['tag:owner']);
    }

    #[Route(path: '', name: RouteAction::Create->value, methods: ['POST'])]
    public function create(
        #[CurrentUser] User $user,
        #[MapRequestPayload(
            serializationContext: ['groups' => ['tag:create']],
            validationGroups: ['Default'],
        )]
        Tag $tag,
    ): JsonResponse {
        if ($this->tagRepository->countByOwner($user) >= 1000) {
            throw new UnprocessableEntityHttpException('You have reached the 1000 tags limit.');
        }

        $existing = $this->tagRepository->findOneByOwnerAndSlug($user, $tag->slug, onlyPublic: false)
            ->getQuery()
            ->getOneOrNullResult()
        ;
        if (!$existing) {
            $existing = $tag;
            $tag->owner = $user;

            try {
                $this->entityManager->persist($tag);
                $this->entityManager->flush();
            } catch (ORMInvalidArgumentException|ORMException $e) {
                throw new UnprocessableEntityHttpException(previous: $e);
            }
        }

        return $this->jsonResponseBuilder->single($existing, ['tag:owner']);
    }

    #[Route(path: '/{slug}', name: RouteAction::Patch->value, methods: ['PATCH'])]
    #[IsGranted(attribute: TagVoter::OWNER, subject: 'tag', statusCode: Response::HTTP_NOT_FOUND)]
    public function patch(
        #[MapEntity(mapping: ['slug' => 'slug'])] Tag $tag,
        #[MapRequestPayload(
            serializationContext: ['groups' => ['tag:owner']],
            validationGroups: ['Default'],
        )]
        Tag $tagPayload,
    ): JsonResponse {
        // Manual merge
        $tag->name = $tagPayload->name ?? $tag->name;
        $tag->meta = array_merge($tag->meta, $tagPayload->meta);

        try {
            $this->entityManager->flush();
        } catch (ORMInvalidArgumentException|ORMException $e) {
            throw new UnprocessableEntityHttpException(previous: $e);
        }

        return $this->jsonResponseBuilder->single($tag, ['tag:owner']);
    }

    #[Route(path: '/{slug}', name: RouteAction::Delete->value, methods: ['DELETE'])]
    #[IsGranted(attribute: TagVoter::OWNER, subject: 'tag', statusCode: Response::HTTP_NOT_FOUND)]
    public function delete(
        #[MapEntity(mapping: ['slug' => 'slug'])] Tag $tag,
    ): JsonResponse {
        $this->entityManager->remove($tag);
        $this->entityManager->flush();

        return new JsonResponse(status: Response::HTTP_NO_CONTENT);
    }
}
