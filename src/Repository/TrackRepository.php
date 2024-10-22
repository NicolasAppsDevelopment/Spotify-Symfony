<?php

namespace App\Repository;

use App\Entity\Track;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class TrackRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Track::class);
    }

    public function isBookmarkedByUser(string $trackId, string $userId): bool
    {
        $qb = $this->createQueryBuilder('t')
            ->innerJoin('t.bookmarkedBy', 'u')
            ->where('u.id = :userId')
            ->andWhere('t.id = :trackId')
            ->setParameter('userId', $userId)
            ->setParameter('trackId', $trackId);

        $query = $qb->getQuery();

        return !is_null($query->setMaxResults(1)->getOneOrNullResult());
    }

    public function getTrackById(string $trackId): ?Track
    {
        return $this->findOneBy(['id' => $trackId]);
    }
}