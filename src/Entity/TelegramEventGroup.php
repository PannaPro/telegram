<?php

namespace App\Entity;

use App\Repository\TelegramEventGroupRepository;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;

#[ORM\Entity(repositoryClass: TelegramEventGroupRepository::class)]
#[ORM\Table(name: 'telegram_event_group')]
#[ORM\UniqueConstraint(name: 'UNIQ_IDENTIFIER_GROUP_CHAT_ID', fields: ['chatId'])]
class TelegramEventGroup
{
    use TimestampableEntity;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: 'bigint', unique: true)]
    private ?int $chatId = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $title = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $type = null;

    #[ORM\Column]
    private ?bool $botIsAdmin = null;

    #[ORM\Column]
    private ?bool $canInvite = null;

    #[ORM\Column]
    private ?bool $isActive = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getChatId(): ?int
    {
        return $this->chatId;
    }

    public function setChatId(int $chatId): static
    {
        $this->chatId = $chatId;

        return $this;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(?string $title): static
    {
        $this->title = $title;

        return $this;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(?string $type): static
    {
        $this->type = $type;

        return $this;
    }

    public function isBotIsAdmin(): ?bool
    {
        return $this->botIsAdmin;
    }

    public function setBotIsAdmin(bool $botIsAdmin): static
    {
        $this->botIsAdmin = $botIsAdmin;

        return $this;
    }

    public function isCanInvite(): ?bool
    {
        return $this->canInvite;
    }

    public function setCanInvite(bool $canInvite): static
    {
        $this->canInvite = $canInvite;

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
}
