<?php

namespace App\Repository;

use App\Entity\Author;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Author>
 */
class AuthorRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Author::class);
    }

    public function save(Author $author, bool $flush = false) : void {
        $em = $this->getEntityManager(); 
        $em->persist($author);
        
        if ($flush) {
            $em->flush();
        }
    }

    public function delete(Author $author, bool $flush = false) : void {
        $em = $this->getEntityManager(); 
        $em->remove($author);
        
        if ($flush) {
            $em->flush();
        }
    }

    public function getAll(int $page = 1, int $limit = 10): array
    {
        $page = max(1, $page);
        $offset = ($page - 1) * $limit;
        $authorsCount = $this->count([]);
        $authors = $this->findBy([], null, $limit, $offset);
        $totalPages = ceil($authorsCount / $limit);

        return [
            'page' => $page,
            'total_page' => $totalPages,
            'authors' => $authors,
        ];
    }
}
