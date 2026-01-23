<?php

namespace App\Entity;

use App\Repository\EventRepository;
use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;

#[ORM\Entity(repositoryClass: EventRepository::class)]
class Event
{
    use TimestampableEntity;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\Column(nullable: true)]
    private ?DateTimeImmutable $periodFrom = null;

    #[ORM\Column(nullable: true)]
    private ?DateTimeImmutable $periodTo = null;

    #[ORM\ManyToOne(inversedBy: 'events')]
    private ?EventType $type = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $groupLink = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $partnerChanelLink = null;

    #[ORM\Column]
    private ?bool $isActive = null;

    #[ORM\OneToOne(cascade: ['persist', 'remove'])]
    private ?TelegramEventGroup $eventGroup = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getPeriodFrom(): ?DateTimeImmutable
    {
        return $this->periodFrom;
    }

    public function setPeriodFrom(?DateTimeImmutable $periodFrom): static
    {
        $this->periodFrom = $periodFrom;

        return $this;
    }

    public function getPeriodTo(): ?DateTimeImmutable
    {
        return $this->periodTo;
    }

    public function setPeriodTo(?DateTimeImmutable $periodTo): static
    {
        $this->periodTo = $periodTo;

        return $this;
    }

    public function getType(): ?EventType
    {
        return $this->type;
    }

    public function setType(?EventType $type): static
    {
        $this->type = $type;

        return $this;
    }

    public function getGroupLink(): ?string
    {
        return $this->groupLink;
    }

    public function setGroupLink(?string $groupLink): static
    {
        $this->groupLink = $groupLink;

        return $this;
    }

    public function getPartnerChanelLink(): ?string
    {
        return $this->partnerChanelLink;
    }

    public function setPartnerChanelLink(?string $partnerChanelLink): static
    {
        $this->partnerChanelLink = $partnerChanelLink;

        return $this;
    }

    public function isActive(): ?bool
    {
        return $this->isActive;
    }

    public function setIsActive(bool $isActive): static
    {
        $this->isActive = $isActive;

        return $this;
    }

    public function getEventGroup(): ?TelegramEventGroup
    {
        return $this->eventGroup;
    }

    public function setEventGroup(?TelegramEventGroup $eventGroup): static
    {
        $this->eventGroup = $eventGroup;

        return $this;
    }
}
