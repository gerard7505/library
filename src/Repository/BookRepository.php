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

    /**
     * Encuentra libros por título (búsqueda parcial)
     *
     * @param string $title
     * @return Book[]
     */
    public function findByTitle(string $title): array
    {
        return $this->createQueryBuilder('b')
            ->where('b.title LIKE :title')
            ->setParameter('title', '%' . $title . '%')
            ->orderBy('b.id', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Encuentra libros por autor
     *
     * @param string $author
     * @return Book[]
     */
    public function findByAuthor(string $author): array
    {
        return $this->createQueryBuilder('b')
            ->where('b.author LIKE :author')
            ->setParameter('author', '%' . $author . '%')
            ->orderBy('b.id', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Encuentra los últimos libros agregados
     *
     * @param int $limit
     * @return Book[]
     */
    public function findLatestBooks(int $limit = 10): array
    {
        return $this->createQueryBuilder('b')
            ->orderBy('b.id', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }
}
