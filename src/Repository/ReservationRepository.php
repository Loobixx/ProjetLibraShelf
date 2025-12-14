<?php

namespace App\Repository;

use App\Entity\Reservation;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Reservation>
 *
 * @method Reservation|null find($id, $lockMode = null, $lockVersion = null)
 * @method Reservation|null findOneBy(array $criteria, array $orderBy = null)
 * @method Reservation[]    findAll()
 * @method Reservation[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ReservationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Reservation::class);
    }

    public function findLateReturns(): array
    {
        return $this->createQueryBuilder('r')
            ->andWhere('r.dateRetourReelle IS NULL') // Pas encore rendu
            ->andWhere('r.dateRetourPrevue < :now')  // Date dépassée
            ->setParameter('now', new \DateTimeImmutable())
            ->getQuery()
            ->getResult();
    }

    public function findDueAt(\DateTimeInterface $dateCible): array
    {
        // On clone pour ne pas modifier l'objet original et on définit l'intervalle de la journée (00:00 à 23:59)
        $start = (clone $dateCible)->setTime(0, 0, 0);
        $end   = (clone $dateCible)->setTime(23, 59, 59);

        return $this->createQueryBuilder('r')
            ->where('r.dateRetourPrevue BETWEEN :start AND :end')
            ->andWhere('r.dateRetourReelle IS NULL') // Pas encore rendu
            ->setParameter('start', $start)
            ->setParameter('end', $end)
            ->getQuery()
            ->getResult();
    }

    public function findOldHistory(\DateTimeInterface $dateLimite): array
    {
        return $this->createQueryBuilder('r')
            ->where('r.dateRetourReelle IS NOT NULL') // C'est rendu
            ->andWhere('r.dateRetourReelle < :limite') // C'est vieux
            ->setParameter('limite', $dateLimite)
            ->getQuery()
            ->getResult();
    }
}
