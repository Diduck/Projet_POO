<?php

namespace App\Repository;

use App\Entity\Book;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Book>
 */
class BookRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Book::class);
    }

    public function findByCategory(int $categoryId): array
    {
        return $this->createQueryBuilder('b')
            ->andWhere('b.category = :cat')
            ->setParameter('cat', $categoryId)
            ->orderBy('b.titre', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function findAvailable(): array
    {
        return $this->findBy(['disponible' => true], ['titre' => 'ASC']);
    }

    public function findByTitre(string $query): array
    {
        return $this->createQueryBuilder('b')
            ->andWhere('b.titre LIKE :q OR b.auteur LIKE :q')
            ->setParameter('q', '%' . $query . '%')
            ->orderBy('b.titre', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
