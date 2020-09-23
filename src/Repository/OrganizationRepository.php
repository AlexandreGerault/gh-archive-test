<?php

namespace App\Repository;

use App\Entity\Organization;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Organization|null find($id, $lockMode = null, $lockVersion = null)
 * @method Organization|null findOneBy(array $criteria, array $orderBy = null)
 * @method Organization[]    findAll()
 * @method Organization[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class OrganizationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Organization::class);
    }

    public function createFromEventIfNotExists($event)
    {
        $o = $this->find($event->actor->id);

        if (!$o) {
            return (new Organization())
                ->setId($event->org->id)
                ->setLogin($event->org->login)
                ->setGravatarId($event->org->gravatar_id !== "" ? $event->org->gravatar_id : null)
                ->setUrl($event->org->url)
                ->setAvatarUrl($event->org->avatar_url)
                ->setEvent($event->org->id);
        }

        return null;
    }
}
