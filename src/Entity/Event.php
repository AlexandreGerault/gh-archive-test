<?php

namespace App\Entity;

use App\Repository\EventRepository;
use DateTimeInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=EventRepository::class)
 */
class Event
{
    /**
     * @ORM\Id
     * @ORM\Column(type="bigint")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $type;

    /**
     * @ORM\Column(type="boolean")
     */
    private $public;

    /**
     * @ORM\Column(type="datetime")
     */
    private $created_at;

    /**
     * @ORM\OneToMany(targetEntity=Actor::class, mappedBy="event")
     */
    private $actor;

    /**
     * @ORM\OneToMany(targetEntity=Repo::class, mappedBy="event")
     */
    private $repo;

    /**
     * @ORM\OneToMany(targetEntity=Organization::class, mappedBy="event")
     */
    private $organization;

    /**
     * @ORM\Column(type="json")
     */
    private $payload = [];

    public function __construct()
    {
        $this->actor = new ArrayCollection();
        $this->repo = new ArrayCollection();
        $this->organization = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId($id): self
    {
        $this->id = $id;

        return $this;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(string $type): self
    {
        $this->type = $type;

        return $this;
    }

    public function getPublic(): ?bool
    {
        return $this->public;
    }

    public function setPublic(bool $public): self
    {
        $this->public = $public;

        return $this;
    }

    public function getCreatedAt(): ?DateTimeInterface
    {
        return $this->created_at;
    }

    public function setCreatedAt(DateTimeInterface $created_at): self
    {
        $this->created_at = $created_at;

        return $this;
    }

    /**
     * @return Collection|Actor[]
     */
    public function getActor(): Collection
    {
        return $this->actor;
    }

    public function addActor(Actor $actor): self
    {
        if (!$this->actor->contains($actor)) {
            $this->actor[] = $actor;
            $actor->setEvent($this);
        }

        return $this;
    }

    public function removeActor(Actor $actor): self
    {
        if ($this->actor->contains($actor)) {
            $this->actor->removeElement($actor);
            // set the owning side to null (unless already changed)
            if ($actor->getEvent() === $this) {
                $actor->setEvent(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Repo[]
     */
    public function getRepo(): Collection
    {
        return $this->repo;
    }

    public function addRepo(Repo $repo): self
    {
        if (!$this->repo->contains($repo)) {
            $this->repo[] = $repo;
            $repo->setEvent($this);
        }

        return $this;
    }

    public function removeRepo(Repo $repo): self
    {
        if ($this->repo->contains($repo)) {
            $this->repo->removeElement($repo);
            // set the owning side to null (unless already changed)
            if ($repo->getEvent() === $this) {
                $repo->setEvent(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Organization[]
     */
    public function getOrganization(): Collection
    {
        return $this->organization;
    }

    public function addOrganization(Organization $organization): self
    {
        if (!$this->organization->contains($organization)) {
            $this->organization[] = $organization;
            $organization->setEvent($this);
        }

        return $this;
    }

    public function removeOrganization(Organization $organization): self
    {
        if ($this->organization->contains($organization)) {
            $this->organization->removeElement($organization);
            // set the owning side to null (unless already changed)
            if ($organization->getEvent() === $this) {
                $organization->setEvent(null);
            }
        }

        return $this;
    }

    public function getPayload(): ?array
    {
        return $this->payload;
    }

    public function setPayload(array $payload): self
    {
        $this->payload = $payload;

        return $this;
    }
}
