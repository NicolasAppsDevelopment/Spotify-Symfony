<?php

namespace App\Repository;

use App\Entity\Track;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\User\UserInterface;

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

    public function getBookmarkedTracks(UserInterface $user): mixed
    {
        $qb = $this->createQueryBuilder('t')
            ->innerJoin('t.bookmarkedBy', 'u')
            ->where('u.id = :userId')
            ->setParameter('userId', $user->getUserIdentifier());

        $query = $qb->getQuery();

        return $query->execute();
    }
}