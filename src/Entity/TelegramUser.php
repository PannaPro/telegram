<?php

namespace App\Entity;

use App\Repository\TelegramUserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;

#[ORM\Entity(repositoryClass: TelegramUserRepository::class)]
#[ORM\Table(name: 'telegram_user')]
#[ORM\UniqueConstraint(name: 'UNIQ_IDENTIFIER_CHAT_ID', fields: ['chatId'])]
class TelegramUser
{
    use TimestampableEntity;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(type: 'bigint', unique: true)]
    private int $chatId;

    #[ORM\Column(type: 'string', length: 100, nullable: true)]
    private ?string $username = null;

    #[ORM\Column(type: 'string', length: 100, nullable: true)]
    private ?string $firstName = null;

    #[ORM\Column(type: 'string', length: 100, nullable: true)]
    private ?string $lastName = null;

    #[ORM\Column]
    private bool $isActive = true;

    #[ORM\Column]
    private bool $isAdmin = false;

    #[ORM\Column]
    private bool $participant = false;

    #[ORM\Column(length: 255)]
    private string $referralLink = 'unknown';

    #[ORM\ManyToOne(targetEntity: self::class, inversedBy: 'referredBy')]
    #[ORM\JoinColumn(name: 'referred_by_id', referencedColumnName: 'id')]
    private ?self $referredByUser = null;

    /**
     * @var Collection<int, self>
     */
    #[ORM\OneToMany(targetEntity: self::class, mappedBy: 'referredByUser')]
    private Collection $referredBy;

    public function __construct()
    {
        $this->referredBy = new ArrayCollection();
    }

    /**
     * @return int|null
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @return int
     */
    public function getChatId(): int
    {
        return $this->chatId;
    }

    /**
     * @param int $chatId
     */
    public function setChatId(int $chatId): static
    {
        $this->chatId = $chatId;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getUsername(): ?string
    {
        return $this->username;
    }

    /**
     * @param string|null $username
     */
    public function setUsername(?string $username): static
    {
        $this->username = $username;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getFirstName(): ?string
    {
        return $this->firstName;
    }

    /**
     * @param string|null $firstName
     */
    public function setFirstName(?string $firstName): static
    {
        $this->firstName = $firstName;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getLastName(): ?string
    {
        return $this->lastName;
    }

    /**
     * @param string|null $lastName
     * @return TelegramUser
     */
    public function setLastName(?string $lastName): static
    {
        $this->lastName = $lastName;

        return $this;
    }

    public function isActive(): bool
    {
        return $this->isActive;
    }

    public function setIsActive(bool $isActive): static
    {
        $this->isActive = $isActive;

        return $this;
    }

    public function isAdmin(): bool
    {
        return $this->isAdmin;
    }

    public function setIsAdmin(bool $isAdmin): static
    {
        $this->isAdmin = $isAdmin;

        return $this;
    }

    public function isParticipant(): bool
    {
        return $this->participant;
    }

    public function setParticipant(bool $participant): static
    {
        $this->participant = $participant;

        return $this;
    }

    public function getReferralLink(): ?string
    {
        return $this->referralLink;
    }

    public function setReferralLink(string $referralLink): static
    {
        $this->referralLink = $referralLink;

        return $this;
    }

    /**
     * Получить пользователя, который пригласил этого пользователя
     */
    public function getReferrer(): ?self
    {
        return $this->referredByUser;
    }

    /**
     * Установить пользователя, который пригласил этого пользователя
     */
    public function setReferrer(?self $referrer): static
    {
        $this->referredByUser = $referrer;

        return $this;
    }

    /**
     * Получить всех пользователей, которых пригласил этот пользователь
     *
     * @return Collection<int, self>
     */
    public function getReferrals(): Collection
    {
        return $this->referredBy;
    }

    /**
     * Добавить пользователя в список приглашенных
     */
    public function addReferral(self $user): static
    {
        if (!$this->referredBy->contains($user)) {
            $this->referredBy->add($user);
            $user->setReferrer($this);
        }

        return $this;
    }

    /**
     * Удалить пользователя из списка приглашенных
     */
    public function removeReferral(self $user): static
    {
        if ($this->referredBy->removeElement($user)) {
            if ($user->getReferrer() === $this) {
                $user->setReferrer(null);
            }
        }

        return $this;
    }
}
