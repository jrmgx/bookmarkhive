<?php

namespace App\Repository;

use App\Entity\Tag;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Tag>
 */
class TagRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Tag::class);
    }

    public function findOneByOwnerAndName(User $owner, string $name): ?Tag
    {
        return $this->createQueryBuilder('t')
            ->andWhere('t.owner = :owner')
            ->setParameter('owner', $owner)
            ->andWhere('t.name = :name')
            ->setParameter('name', $name)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
}
