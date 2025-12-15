<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\TagRepository;
use App\Response\JsonResponseBuilder;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;

abstract class TagController extends AbstractController
{
    public function __construct(
        protected readonly TagRepository $tagRepository,
        protected readonly EntityManagerInterface $entityManager,
        protected readonly JsonResponseBuilder $jsonResponseBuilder,
    ) {
    }

    /**
     * @param list<string> $groups
     */
    public function collectionCommon(User $user, array $groups, bool $onlyPublic): JsonResponse
    {
        $tags = $this->tagRepository->findByOwner($user, onlyPublic: $onlyPublic)
            ->getQuery()
            ->getResult()
        ;

        return $this->jsonResponseBuilder->collection(
            $tags, $groups, [
                'prevPage' => false,
                'nextPage' => false,
                'total' => \count($tags),
            ]
        );
    }
}
