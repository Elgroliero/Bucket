<?php

namespace App\Repository;

use App\Entity\Wish;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

class WishRepository extends ServiceEntityRepository
{

    public function __construct(\Doctrine\Persistence\ManagerRegistry $registry)
    {
        parent::__construct($registry, \App\Entity\Wish::class);
    }

    public function getWishById(int $id): ?Wish
    {
        $query = $this->createQueryBuilder('wish')
            ->where('wish.id = :id')->setParameter('id', $id)
            ->andWhere('wish.isPublished = :published')->setParameter('published', true);

        return $query->getQuery()->getOneOrNullResult();
    }

    public function getWishByPage(int $page, int $maxPerPage): array
    {
        $query = $this->createQueryBuilder('wish')
            ->Where('wish.isPublished = 1')
            ->orderBy('wish.id', 'ASC');

        //Pagination
        $query->setMaxResults($maxPerPage);
        $query->setFirstResult(($page - 1) * $maxPerPage);
        return $query->getQuery()->getResult();
    }

    public function getAllPublishedWishes()
    {
        $query = $this->createQueryBuilder('wish')
            ->andWhere('wish.isPublished = 1')
            ->orderBy('wish.id', 'ASC');
        return $query->getQuery()->getResult();
    }

    public function findPublishedWishesWithCategories(int $page, int $maxPerPage): array

    {
        // crée un query builder et on donne l'alias de w à Wish
        $query = $this->createQueryBuilder('w');
        $query->leftjoin('w.category', 'c')
            ->addSelect('c');
        $query->setMaxResults($maxPerPage);
        $query->setFirstResult(($page - 1) * $maxPerPage);
        $query->andWhere('w.isPublished = 1');
        $query->orderBy('w.dateCreated', 'DESC');
        $query = $query->getQuery();
        return $query->getResult();
    }

}