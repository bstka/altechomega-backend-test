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

    public function save(Book $book, bool $flush = false) : void {
        $em = $this->getEntityManager(); 
        $em->persist($book);
        
        if ($flush) {
            $em->flush();
        }
    }

    public function delete(Book $book, bool $flush = false) : void {
        $em = $this->getEntityManager(); 
        $em->remove($book);
        
        if ($flush) {
            $em->flush();
        }
    }

    public function getAll(int $page = 1, int $limit = 10): array
    {
        $page = max(1, $page);
        $offset = ($page - 1) * $limit;
        $bookCount = $this->count([]);
        $books = $this->findBy([], null, $limit, $offset);
        $totalPages = ceil($bookCount / $limit);

        return [
            'page' => $page,
            'total_page' => $totalPages,
            'books' => $books,
        ];
    }
}
