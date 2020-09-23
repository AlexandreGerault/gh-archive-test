<?php

namespace App\GithubArchiveServices;


use App\Repository\ActorRepository;
use App\Repository\EventRepository;
use App\Repository\OrganizationRepository;
use App\Repository\RepoRepository;

use Doctrine\ORM\EntityManagerInterface;

class GithubArchiveImporterSQL implements GithubArchiveImporterInterface
{
    private EntityManagerInterface $em;
    private EventRepository $eventRepository;
    private ActorRepository $actorRepository;
    private RepoRepository $repoRepository;
    private OrganizationRepository $organizationRepository;

    /**
     * GithubArchiveImporterSQL constructor.
     * @param EntityManagerInterface $em
     * @param EventRepository $eventRepository
     * @param ActorRepository $actorRepository
     * @param RepoRepository $repoRepository
     * @param OrganizationRepository $organizationRepository
     */
    public function __construct(
        EntityManagerInterface $em,
        EventRepository $eventRepository,
        ActorRepository $actorRepository,
        RepoRepository $repoRepository,
        OrganizationRepository $organizationRepository
    ) {
        $this->em = $em;
        $this->em->getConnection()->getConfiguration()->setSQLLogger(null);
        $this->eventRepository = $eventRepository;
        $this->actorRepository = $actorRepository;
        $this->repoRepository = $repoRepository;
        $this->organizationRepository = $organizationRepository;
    }

    /**
     * Persist all the
     *
     * @param array $events
     */
    public function import(array &$events): void
    {
        $i = 1;
        $batchSize = 10000;
        foreach ($events as $event) {
            $dbEvent = $this->eventRepository->findOrCreate($event);
            $this->em->persist($dbEvent);

            $actor = $this->actorRepository->createFromEventIfNotExists($event, $dbEvent);
            if ($actor) {
                $this->em->persist($actor);
            }

            $repo = $this->repoRepository->createFromEventIfNotExists($event, $dbEvent);
            if ($repo) {
                $this->em->persist($repo);
            }

            if (isset($event->org)) {
                $org = $this->organizationRepository->createFromEventIfNotExists($event, $dbEvent);
                if ($org) {
                    $this->em->persist($org);
                }
            }

            // Organization missing

            if ($i % $batchSize === 0) {
                $this->em->flush();
                $this->em->clear();
            }

            $i++;
        }

        $this->em->flush();
        $this->em->clear();
    }
}