<?php

namespace App\Repository;

use App\Entity\Repo;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Repo|null find($id, $lockMode = null, $lockVersion = null)
 * @method Repo|null findOneBy(array $criteria, array $orderBy = null)
 * @method Repo[]    findAll()
 * @method Repo[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class RepoRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Repo::class);
    }

    public function createFromEventIfNotExists($event): ?Repo
    {
        $r = $this->find($event->repo->id);

        if (!$r) {
            return (new Repo())
                ->setId($event->repo->id)
                ->setEvent($event->id)
                ->setUrl($event->repo->url)
                ->setName($event->repo->name);
        }

        return null;
    }
}
