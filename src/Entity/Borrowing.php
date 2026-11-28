<?php

namespace App\Entity;

use App\Repository\BorrowingRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

#[ORM\Entity(repositoryClass: BorrowingRepository::class)]
class Borrowing
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    #[Assert\NotNull]
    private ?\DateTimeImmutable $borrowedAt = null;

    #[ORM\Column]
    #[Assert\NotNull]
    #[Assert\GreaterThan(propertyPath: 'borrowedAt', message: 'The due date must be after the borrow date')]
    private ?\DateTimeImmutable $dueDate = null;

    #[ORM\Column(nullable: true)]
    #[Assert\GreaterThanOrEqual(propertyPath: 'borrowedAt', message: 'The return date cannot be before the borrow date')]
    private ?\DateTimeImmutable $returnedAt = null;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'borrowings')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $user = null;

    #[ORM\ManyToOne(targetEntity: Book::class, inversedBy: 'borrowings')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Book $book = null;

    #[ORM\OneToOne(targetEntity: Reservation::class, inversedBy: 'borrowing')]
    #[ORM\JoinColumn(nullable: true, onDelete: 'SET NULL')]
    private ?Reservation $reservation = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(int $id): static
    {
        $this->id = $id;

        return $this;
    }

    public function getBorrowedAt(): ?\DateTimeImmutable
    {
        return $this->borrowedAt;
    }

    public function setBorrowedAt(\DateTimeImmutable $borrowedAt): static
    {
        $this->borrowedAt = $borrowedAt;

        return $this;
    }

    public function getDueDate(): ?\DateTimeImmutable
    {
        return $this->dueDate;
    }

    public function setDueDate(\DateTimeImmutable $dueDate): static
    {
        $this->dueDate = $dueDate;

        return $this;
    }

    public function getReturnedAt(): ?\DateTimeImmutable
    {
        return $this->returnedAt;
    }

    public function setReturnedAt(?\DateTimeImmutable $returnedAt): static
    {
        $this->returnedAt = $returnedAt;

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

    public function getReservation(): ?Reservation
    {
        return $this->reservation;
    }

    public function setReservation(?Reservation $reservation): static
    {
        $this->reservation = $reservation;

        return $this;
    }

    #[Assert\Callback]
    public function validateReservation(ExecutionContextInterface $context): void
    {
        // Nothing to check without a reservation, or while member/book are
        // still missing (their own NotBlank constraints report that).
        if ($this->reservation === null || $this->user === null || $this->book === null) {
            return;
        }

        if ($this->reservation->getUser()?->getId() !== $this->user->getId()) {
            $context->buildViolation('This reservation belongs to another member.')
                ->atPath('reservation')
                ->addViolation();
        }

        if ($this->reservation->getBook()?->getId() !== $this->book->getId()) {
            $context->buildViolation('This reservation is for a different book.')
                ->atPath('reservation')
                ->addViolation();
        }

        // A non-pending reservation is only acceptable if it is already
        // linked to THIS borrowing (editing an existing borrowing).
        if ($this->reservation->getStatus() !== Reservation::STATUS_PENDING
            && $this->reservation->getBorrowing() !== $this) {
            $context->buildViolation('Only pending reservations can be used.')
                ->atPath('reservation')
                ->addViolation();
        }
    }
}