<?php

namespace App\GithubArchiveServices;

use DateTime;
use Doctrine\DBAL\DBALException;
use Doctrine\ORM\EntityManagerInterface;

class GithubArchiveImporterSQL implements GithubArchiveImporterInterface
{
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
     * Persist all the events. Do not duplicate entities.
     *
     * @param array $events
     * @throws DBALException
     */
    public function import(array &$events): void
    {
        $eventStatement = $this->em->getConnection()->prepare(<<<SQL
INSERT INTO event (id, type, public, created_at, payload)
VALUES (:id, :type, :public, :created_at, :payload)
ON DUPLICATE KEY UPDATE `id`=:id
SQL);
        $actorStatement = $this->em->getConnection()->prepare(<<<SQL
INSERT INTO actor (id, event_id, login, gravatar_id, url, avatar_url)
VALUES (:id, :event_id, :login, :gravatar_id, :url, :avatar_url)
ON DUPLICATE KEY UPDATE `id`=:id
SQL);
        $repoStatement = $this->em->getConnection()->prepare(<<<SQL
INSERT INTO repo (id, event_id, name, url)
VALUES (:id, :event_id, :name, :url)
ON DUPLICATE KEY UPDATE `id`=:id
SQL);
        $orgStatement = $this->em->getConnection()->prepare(<<<SQL
INSERT INTO organization (id, event_id, login, gravatar_id, url, avatar_url)
VALUES (:id, :event_id, :login, :gravatar_id, :url, :avatar_url)
ON DUPLICATE KEY UPDATE `id`=:id
SQL);

        foreach ($events as $event) {
            // Insert event first
            $public = $event->public ? "1" : "0";
            $created_at = DateTime::createFromFormat(
                'Y-m-d\TH:i:s\Z',
                $event->created_at
            )->format('Y-m-d H:i:s');
            $payload = json_encode($event->payload);
            $eventStatement->bindParam(':id', $event->id);
            $eventStatement->bindParam(':type', $event->type);
            $eventStatement->bindParam(':public', $public);
            $eventStatement->bindParam(':created_at', $created_at);
            $eventStatement->bindParam(':payload', $payload);
            $eventStatement->execute();

            // Then insert relations
            $actorGravatar = $event->actor->gravatar_id === '' ? 0 : (int) $event->actor->gravatar_id;
            $actorStatement->bindParam(':id', $event->actor->id);
            $actorStatement->bindParam(':event_id', $event->id);
            $actorStatement->bindParam(':login', $event->actor->login);
            $actorStatement->bindParam(':gravatar_id', $actorGravatar);
            $actorStatement->bindParam(':url', $event->actor->url);
            $actorStatement->bindParam(':avatar_url', $event->actor->avatar_url);
            $actorStatement->execute();

            $repoStatement->bindParam(':id', $event->repo->id);
            $repoStatement->bindParam(':event_id', $event->id);
            $repoStatement->bindParam(':name', $event->repo->name);
            $repoStatement->bindParam(':url', $event->repo->url);
            $repoStatement->execute();

            if (isset($event->org)) {
                $orgGravatar = $event->org->gravatar_id === '' ? null : (int) $event->org->gravatar_id;
                $orgStatement->bindParam(':id', $event->org->id);
                $orgStatement->bindParam(':event_id', $event->id);
                $orgStatement->bindParam(':login', $event->org->login);
                $orgStatement->bindParam(':gravatar_id', $orgGravatar);
                $orgStatement->bindParam(':url', $event->org->url);
                $orgStatement->bindParam(':avatar_url', $event->org->avatar_url);
                $orgStatement->execute();
            }
        }
    }
}