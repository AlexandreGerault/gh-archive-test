<?php

namespace App\GithubArchiveServices;

use App\Entity\Actor;
use App\Entity\Event;
use App\Entity\Repo;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;

class GithubArchiveImporterSQL implements GithubArchiveImporterInterface
{
    private const GITHUB_EVENT_DATETIME_FORMAT = 'Y-m-d\TH:i:s\Z';
    private EntityManagerInterface $em;

    /**
     * GithubArchiveImporterSQL constructor.
     * @param EntityManagerInterface $em
     */
    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
        $this->em->getConnection()->getConfiguration()->setSQLLogger(null);
    }

    /**
     * Persist all the
     *
     * @param array $events
     */
    public function import(array &$events): void
    {
        $i = 1;
        $batchSize = 200;
        foreach ($events as $event) {
            $dbEvent = (new Event())
                ->setType($event->type)
                ->setPayload(json_decode(json_encode($event->payload), true))
                ->setPublic($event->public)
                ->setCreatedAt(
                    DateTime::createFromFormat(
                        self::GITHUB_EVENT_DATETIME_FORMAT,
                        $event->created_at
                    )
                )
            ;
            $this->em->persist($dbEvent);

            $actor = (new Actor())
                ->setLogin($event->actor->login)
                ->setGravatarId(
                    $event->actor->gravatar_id !== "" ? $event->actor->gravatar_id : null
                )
                ->setUrl($event->actor->url)
                ->setAvatarUrl($event->actor->avatar_url)
                ->setEvent($dbEvent)
            ;
            $this->em->persist($actor);

            $repo = (new Repo())
                ->setName($event->repo->name)
                ->setUrl($event->repo->url)
                ->setEvent($dbEvent)
            ;
            $this->em->persist($repo);

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