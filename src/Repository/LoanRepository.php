<?php

namespace App\Repository;

use App\Entity\Loan;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Loan>
 */
class LoanRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Loan::class);
    }

    public function findByUser(User $user): array
    {
        return $this->createQueryBuilder('l')
            ->andWhere('l.user = :user')
            ->setParameter('user', $user)
            ->orderBy('l.dateEmprunt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function findActiveLoansForUser(User $user): array
    {
        return $this->createQueryBuilder('l')
            ->andWhere('l.user = :user')
            ->andWhere('l.statut != :statut')
            ->setParameter('user', $user)
            ->setParameter('statut', Loan::STATUS_RENDU)
            ->orderBy('l.dateRetourPrevue', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function countActive(): int
    {
        return (int) $this->createQueryBuilder('l')
            ->select('COUNT(l.id)')
            ->andWhere('l.statut != :statut')
            ->setParameter('statut', Loan::STATUS_RENDU)
            ->getQuery()
            ->getSingleScalarResult();
    }
}
