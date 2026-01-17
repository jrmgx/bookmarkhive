<?php

namespace App\Service;

use App\Entity\Bookmark;
use App\Entity\InstanceTag;
use App\Repository\InstanceTagRepository;
use Doctrine\ORM\EntityManagerInterface;

// TODO add tests
final readonly class InstanceTagService
{
    public function __construct(
        private InstanceTagRepository $instanceTagRepository,
        private EntityManagerInterface $entityManager,
    ) {
    }

    public function synchronize(Bookmark $bookmark): void
    {
        $bookmark->instanceTags->clear();
        foreach ($bookmark->userTags as $userTag) {
            if (!$userTag->isPublic) {
                continue;
            }
            $instanceTag = $this->instanceTagRepository->findBySlug($userTag->slug);
            if (!$instanceTag) {
                $instanceTag = new InstanceTag();
                $instanceTag->name = $userTag->name;
                $this->entityManager->persist($instanceTag);
            }
            $bookmark->instanceTags->add($instanceTag);
        }
    }
}
