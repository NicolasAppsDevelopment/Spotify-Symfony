<?php

namespace App\Repository;

use App\Entity\Artist;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class ArtistRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Artist::class);
    }

    public function isBookmarkedByUser(string $artistId, string $userId): bool
    {
        $qb = $this->createQueryBuilder('a')
            ->innerJoin('a.bookmarkedBy', 'u')
            ->where('u.id = :userId')
            ->andWhere('a.id = :artistId')
            ->setParameter('userId', $userId)
            ->setParameter('artistId', $artistId);

        $query = $qb->getQuery();

        return !is_null($query->getOneOrNullResult());
    }

    public function getArtistById(string $artistId): ?Artist
    {
        return $this->findOneBy(['id' => $artistId]);
    }
}