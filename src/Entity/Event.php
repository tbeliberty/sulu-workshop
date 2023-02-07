<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\EventRepository;
use DateTimeImmutable;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Sulu\Bundle\MediaBundle\Entity\MediaInterface;

#[ORM\Entity(repositoryClass: EventRepository::class)]
class Event
{
    final public const RESOURCE_KEY = 'events';

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::INTEGER)]
    private ?int $id = null;

    #[ORM\Column(type: Types::BOOLEAN)]
    private bool $enabled = false;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true)]
    private ?DateTimeImmutable $startDate = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true)]
    private ?DateTimeImmutable $endDate = null;

    #[ORM\Column(type: Types::STRING, length: 255, nullable: true)]
    private ?string $location = null;

    #[ORM\ManyToOne(targetEntity: MediaInterface::class)]
    #[ORM\JoinColumn(onDelete: 'SET NULL')]
    private ?MediaInterface $image = null;

    /**
     * @var Collection<string, EventTranslation>
     */
    #[ORM\OneToMany(targetEntity: EventTranslation::class, mappedBy: 'event', cascade: ['persist'], indexBy: 'locale')]
    private Collection $translations;

    private string $locale;

    #[ORM\OneToMany(mappedBy: 'event', targetEntity: EventRegistration::class, orphanRemoval: true)]
    private Collection $eventRegistration;

    public function __construct()
    {
        $this->translations = new ArrayCollection();
        $this->eventRegistration = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function isEnabled(): bool
    {
        return $this->enabled;
    }

    public function setEnabled(bool $enabled): self
    {
        $this->enabled = $enabled;

        return $this;
    }

    public function getStartDate(): ?DateTimeImmutable
    {
        return $this->startDate;
    }

    public function setStartDate(?DateTimeImmutable $startDate): self
    {
        $this->startDate = $startDate;

        return $this;
    }

    public function getEndDate(): ?DateTimeImmutable
    {
        return $this->endDate;
    }

    public function setEndDate(?DateTimeImmutable $endDate): self
    {
        $this->endDate = $endDate;

        return $this;
    }

    public function getLocation(): ?string
    {
        return $this->location;
    }

    public function setLocation(?string $location): self
    {
        $this->location = $location;

        return $this;
    }

    public function getImage(): ?MediaInterface
    {
        return $this->image;
    }

    public function setImage(?MediaInterface $image): self
    {
        $this->image = $image;

        return $this;
    }

    public function getTitle(): ?string
    {
        $translation = $this->getTranslation($this->locale);
        if (!$translation instanceof EventTranslation) {
            return null;
        }

        return $translation->getTitle();
    }

    public function setTitle(string $title): self
    {
        $translation = $this->getTranslation($this->locale);
        if (!$translation instanceof EventTranslation) {
            $translation = $this->createTranslation($this->locale);
        }

        $translation->setTitle($title);

        return $this;
    }

    public function getTeaser(): ?string
    {
        $translation = $this->getTranslation($this->locale);
        if (!$translation instanceof EventTranslation) {
            return null;
        }

        return $translation->getTeaser();
    }

    public function setTeaser(string $teaser): self
    {
        $translation = $this->getTranslation($this->locale);
        if (!$translation instanceof EventTranslation) {
            $translation = $this->createTranslation($this->locale);
        }

        $translation->setTeaser($teaser);

        return $this;
    }

    public function getDescription(): ?string
    {
        $translation = $this->getTranslation($this->locale);
        if (!$translation instanceof EventTranslation) {
            return null;
        }

        return $translation->getDescription();
    }

    public function setDescription(string $description): self
    {
        $translation = $this->getTranslation($this->locale);
        if (!$translation instanceof EventTranslation) {
            $translation = $this->createTranslation($this->locale);
        }

        $translation->setDescription($description);

        return $this;
    }

    public function getLocale(): string
    {
        return $this->locale;
    }

    public function setLocale(string $locale): self
    {
        $this->locale = $locale;

        return $this;
    }

    /**
     * @return EventTranslation[]
     */
    public function getTranslations(): array
    {
        return $this->translations->toArray();
    }

    protected function getTranslation(string $locale): ?EventTranslation
    {
        if (!$this->translations->containsKey($locale)) {
            return null;
        }

        return $this->translations->get($locale);
    }

    protected function createTranslation(string $locale): EventTranslation
    {
        $translation = new EventTranslation($this, $locale);
        $this->translations->set($locale, $translation);

        return $translation;
    }

    /**
     * @return Collection<int, EventRegistration>
     */
    public function getEventRegistration(): Collection
    {
        return $this->eventRegistration;
    }

    public function addEventRegistration(EventRegistration $eventRegistration): self
    {
        if (!$this->eventRegistration->contains($eventRegistration)) {
            $this->eventRegistration->add($eventRegistration);
            $eventRegistration->setEvent($this);
        }

        return $this;
    }

    public function removeEventRegistration(EventRegistration $eventRegistration): self
    {
        $this->eventRegistration->removeElement($eventRegistration);

        return $this;
    }
}
