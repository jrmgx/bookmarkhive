<?php

namespace App\Repository;

use App\Entity\FileObject;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<FileObject>
 */
class FileObjectRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, FileObject::class);
    }

    public function findOneById(string $id): ?FileObject
    {
        return $this->createQueryBuilder('fo')
            ->andWhere('fo.id = :id')
            ->setParameter('id', $id)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
}
