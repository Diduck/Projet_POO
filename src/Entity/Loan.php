<?php

namespace App\Entity;

use App\Repository\LoanRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: LoanRepository::class)]
class Loan
{
    const STATUS_EN_COURS = 'en_cours';
    const STATUS_RENDU = 'rendu';
    const STATUS_EN_RETARD = 'en_retard';

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: 'datetime')]
    private ?\DateTimeInterface $dateEmprunt = null;

    #[ORM\Column(type: 'date')]
    #[Assert\NotNull(message: 'La date de retour prévue est obligatoire.')]
    private ?\DateTimeInterface $dateRetourPrevue = null;

    #[ORM\Column(length: 20)]
    private string $statut = self::STATUS_EN_COURS;

    #[ORM\ManyToOne(inversedBy: 'loans')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $user = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?Book $book = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDateEmprunt(): ?\DateTimeInterface
    {
        return $this->dateEmprunt;
    }

    public function setDateEmprunt(\DateTimeInterface $dateEmprunt): static
    {
        $this->dateEmprunt = $dateEmprunt;
        return $this;
    }

    public function getDateRetourPrevue(): ?\DateTimeInterface
    {
        return $this->dateRetourPrevue;
    }

    public function setDateRetourPrevue(?\DateTimeInterface $dateRetourPrevue): static
    {
        $this->dateRetourPrevue = $dateRetourPrevue;
        return $this;
    }

    public function getStatut(): string
    {
        return $this->statut;
    }

    public function setStatut(string $statut): static
    {
        $this->statut = $statut;
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

    public function isOverdue(): bool
    {
        if ($this->statut === self::STATUS_RENDU) {
            return false;
        }
        return $this->dateRetourPrevue < new \DateTime('today');
    }

    public static function getStatutLabel(string $statut): string
    {
        return match($statut) {
            self::STATUS_EN_COURS => 'En cours',
            self::STATUS_RENDU => 'Rendu',
            self::STATUS_EN_RETARD => 'En retard',
            default => $statut,
        };
    }
}
