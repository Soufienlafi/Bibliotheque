<?php

namespace App\Entity;

use App\Repository\ReservationRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

#[ORM\Entity(repositoryClass: ReservationRepository::class)]
#[UniqueEntity(fields: ['user', 'book', 'status'], message: 'This member already has this reservation.', errorPath: 'book')]
class Reservation
{
    public const STATUS_PENDING   = 'pending';
    public const STATUS_FULFILLED = 'fulfilled';
    public const STATUS_CANCELLED = 'cancelled';
    public const STATUS_EXPIRED   = 'expired';

    public const STATUSES = [
        'Pending'   => self::STATUS_PENDING,
        'Fulfilled' => self::STATUS_FULFILLED,
        'Cancelled' => self::STATUS_CANCELLED,
        'Expired'   => self::STATUS_EXPIRED,
    ];

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    #[Assert\NotNull]
    private ?\DateTimeImmutable $reservedAt = null;

    #[ORM\Column]
    #[Assert\NotNull]
    #[Assert\GreaterThan(propertyPath: 'reservedAt', message: 'The expiry date must be after the reservation date')]
    private ?\DateTimeImmutable $expiresAt = null;

    #[ORM\Column(length: 20)]
    #[Assert\Choice(choices: self::STATUSES, message: 'Invalid status')]
    private ?string $status = self::STATUS_PENDING;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'reservations')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $user = null;

    #[ORM\ManyToOne(targetEntity: Book::class, inversedBy: 'reservations')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Book $book = null;

    #[ORM\OneToOne(targetEntity: Borrowing::class, mappedBy: 'reservation')]
    private ?Borrowing $borrowing = null;


    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(int $id): static
    {
        $this->id = $id;

        return $this;
    }

    public function getReservedAt(): ?\DateTimeImmutable
    {
        return $this->reservedAt;
    }

    public function setReservedAt(\DateTimeImmutable $reservedAt): static
    {
        $this->reservedAt = $reservedAt;

        return $this;
    }

    public function getExpiresAt(): ?\DateTimeImmutable
    {
        return $this->expiresAt;
    }

    public function setExpiresAt(\DateTimeImmutable $expiresAt): static
    {
        $this->expiresAt = $expiresAt;

        return $this;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(string $status): static
    {
        $this->status = $status;

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): static
    {
        $this->user = $user;

        return $this;
    }

    public function getBook(): ?Book
    {
        return $this->book;
    }

    public function setBook(?Book $book): static
    {
        $this->book = $book;

        return $this;
    }

    public function getBorrowing(): ?Borrowing
    {
        return $this->borrowing;
    }

    public function setBorrowing(?Borrowing $borrowing): static
    {
        // unset the owning side of the relation if necessary
        if ($borrowing === null && $this->borrowing !== null) {
            $this->borrowing->setReservation(null);
        }

        // set the owning side of the relation if necessary
        if ($borrowing !== null && $borrowing->getReservation() !== $this) {
            $borrowing->setReservation($this);
        }

        $this->borrowing = $borrowing;

        return $this;
    }
}
