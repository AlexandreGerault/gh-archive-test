<?php

namespace App\Tests\Integration;

use App\Entity\Actor;
use App\Entity\Event;
use App\Entity\Organization;
use App\Entity\Repo;
use App\GithubArchiveServices\GithubArchiveImporterSQL;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class GithubArchiveImporterSQLTest extends KernelTestCase
{
    private EntityManagerInterface $em;

    public function setUp()
    {
        parent::setUp();

        $kernel = self::bootKernel();
        $this->em = $kernel->getContainer()->get('doctrine')->getManager();
    }

    public function testImportActuallyImportAnEventIntoDatabase()
    {
        // Test initialization
        $payload = json_encode([]);
        $actor = json_encode([
            "id" => 1,
            "login" => "user",
            "gravatar_id" => "",
            "avatar_url" => "https://avatars1.githubusercontent.com/u/3494871",
            "url" => "https://github.com/AlexandreGerault"
        ]);
        $repo = json_encode([
            "id" => 1,
            "name" => "Team-Founder-Website",
            "url" => "https://github.com/team-founder/team-founder-website"
        ]);
        $org = json_encode([
            "id" => 1,
            "login" => "Team-Founder",
            "gravatar_id" => "",
            "avatar_url" => "https://avatars2.githubusercontent.com/u/69220481",
            "url" => "https://github.com/team-founder"
        ]);

        $events = [
            json_decode("{
                \"id\": 1,
                \"type\": \"CreateEvent\",
                \"payload\": $payload,
                \"created_at\": \"2016-03-01T00:00:00Z\",
                \"public\": \"true\",
                \"actor\": $actor,
                \"org\": $org,
                \"repo\": $repo
            }")
        ];
        $importer = new GithubArchiveImporterSQL(
            $this->em,
            $this->em->getRepository(Event::class),
            $this->em->getRepository(Actor::class),
            $this->em->getRepository(Repo::class),
            $this->em->getRepository(Organization::class)
        );

        // Test action
        $importer->import($events);
        $importedEvent = $this->em->getRepository(Event::class)->find(1);

        // Test assertions
        $this->assertSame(1, $importedEvent->getId());
        $this->assertSame("CreateEvent", $importedEvent->getType());
    }
    
    public function testItCanHandleDuplicityWithBatchProcessing()
    {
        // Test initialization
        $payload = json_encode([]);
        $actor = json_encode([
            "id" => 1,
            "login" => "user",
            "gravatar_id" => "",
            "avatar_url" => "https://avatars1.githubusercontent.com/u/3494871",
            "url" => "https://github.com/AlexandreGerault"
        ]);
        $repo = json_encode([
            "id" => 1,
            "name" => "Team-Founder-Website",
            "url" => "https://github.com/team-founder/team-founder-website"
        ]);
        $org = json_encode([
            "id" => 1,
            "login" => "Team-Founder",
            "gravatar_id" => "",
            "avatar_url" => "https://avatars2.githubusercontent.com/u/69220481",
            "url" => "https://github.com/team-founder"
        ]);

        $events = [
            json_decode("{
                \"id\": 1,
                \"type\": \"CreateEvent\",
                \"payload\": $payload,
                \"created_at\": \"2016-03-01T00:00:00Z\",
                \"public\": \"true\",
                \"actor\": $actor,
                \"org\": $org,
                \"repo\": $repo
            }"),
            json_decode("{
                \"id\": 2,
                \"type\": \"CreateEvent\",
                \"payload\": $payload,
                \"created_at\": \"2016-03-01T00:00:00Z\",
                \"public\": \"true\",
                \"actor\": $actor,
                \"org\": $org,
                \"repo\": $repo
            }"),
            json_decode("{
                \"id\": 3,
                \"type\": \"CreateEvent\",
                \"payload\": $payload,
                \"created_at\": \"2016-03-01T00:00:00Z\",
                \"public\": \"true\",
                \"actor\": $actor,
                \"org\": $org,
                \"repo\": $repo
            }"),
        ];


        $importer = new GithubArchiveImporterSQL(
            $this->em,
            $this->em->getRepository(Event::class),
            $this->em->getRepository(Actor::class),
            $this->em->getRepository(Repo::class),
            $this->em->getRepository(Organization::class)
        );

        // Test actions
        $importer->import($events);
        $importedEvents = $this->em->getRepository(Event::class)->findAll();
        $dbActor = $this->em->getRepository(Actor::class)->find(1);
        $dbOrg = $this->em->getRepository(Organization::class)->find(1);

        // Test assertions
        $this->assertCount(3, $importedEvents);
        $this->assertCount(3, $dbActor->getEvents());
        $this->assertCount(3, $dbOrg->getEvents());
    }
}