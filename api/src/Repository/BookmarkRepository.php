<?php

namespace App\Repository;

use App\Entity\Bookmark;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Bookmark>
 */
class BookmarkRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Bookmark::class);
    }

    public function findOneById(string $id): ?Bookmark
    {
        return $this->createQueryBuilder('b')
            ->andWhere('b.id = :id')
            ->setParameter('id', $id)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }

    public function findByOwner(User $owner, bool $onlyPublic): QueryBuilder
    {
        $qb = $this->createQueryBuilder('b')
            ->andWhere('b.owner = :owner')
            ->setParameter('owner', $owner)
            ->addOrderBy('b.id', 'DESC')
            ->andWhere('b.outdated = false')
        ;

        return $onlyPublic ? $qb->andWhere('b.isPublic = true') : $qb;
    }

    public function findOneByOwnerAndId(User $owner, string $id, bool $onlyPublic): QueryBuilder
    {
        $qb = $this->createQueryBuilder('b')
            ->andWhere('b.owner = :owner')
            ->setParameter('owner', $owner)
            ->andWhere('b.id = :id')
            ->setParameter('id', $id)
        ;

        return $onlyPublic ? $qb->andWhere('b.isPublic = true') : $qb;
    }

    public function findLastOneByOwnerAndUrl(User $owner, string $normalizedUrl): ?Bookmark
    {
        return $this->createQueryBuilder('b')
            ->andWhere('b.owner = :owner')
            ->setParameter('owner', $owner)
            ->andWhere('b.url = :url')
            ->setParameter('url', $normalizedUrl)
            ->orderBy('b.id', 'DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }

    /**
     * Apply filters:
     * - tags: all given tags must be present and extra tags can be present
     * - search: fulltext search in title, url and description TODO
     *
     * @param array<string> $tagNames
     */
    public function applyFilters(QueryBuilder $qb, array $tagNames, bool $onlyPublic): QueryBuilder
    {
        if (0 === \count($tagNames)) {
            return $qb;
        }

        $qb = $qb
            ->join('b.tags', 't')
            ->andWhere('t.name IN (:tagNames)')
            ->setParameter('tagNames', $tagNames)
            ->groupBy('b.id')
            ->having('COUNT(DISTINCT t.id) = :tagCount')
            ->setParameter('tagCount', \count($tagNames))
        ;

        return $onlyPublic ? $qb->andWhere('t.isPublic = true') : $qb;
    }
}
