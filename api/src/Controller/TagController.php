<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\InstanceTagRepository;
use App\Repository\UserTagRepository;
use App\Response\JsonResponseBuilder;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;

abstract class TagController extends AbstractController
{
    public function __construct(
        protected readonly UserTagRepository $userTagRepository,
        protected readonly InstanceTagRepository $instanceTagRepository,
        protected readonly EntityManagerInterface $entityManager,
        protected readonly JsonResponseBuilder $jsonResponseBuilder,
    ) {
    }

    /**
     * @param list<string> $groups
     */
    public function collectionCommon(User $user, array $groups, bool $onlyPublic): JsonResponse
    {
        $tags = $this->userTagRepository->findByOwner($user, onlyPublic: $onlyPublic)
            ->getQuery()
            ->getResult()
        ;

        return $this->jsonResponseBuilder->collection(
            $tags, $groups, ['total' => \count($tags)]
        );
    }
}
