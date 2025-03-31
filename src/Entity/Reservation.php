<?php
// src/Entity/Reservation.php
namespace App\Entity;

use App\Repository\ReservationRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ReservationRepository::class)]
#[ORM\Table(name: "reservation")]  // Asegurarse de que el nombre de la tabla es correcto
class Reservation
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: "integer")]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: "App\Entity\Book")]
    #[ORM\JoinColumn(name: "book_id", referencedColumnName: "id", nullable: false)]
    private ?Book $book = null;

    #[ORM\Column(type: "string", length: 255, name: "user_email")]
    private ?string $userEmail = null;

    // Cambiado de "date" a "datetime" para manejar fecha y hora
    #[ORM\Column(type: "datetime", name: "start_date")]  
    private ?\DateTime $startDate = null;

    #[ORM\Column(type: "datetime", name: "end_date")]  // Cambiado a "datetime"
    private ?\DateTime $endDate = null;

    // Métodos getter y setter
    public function getId(): ?int
    {
        return $this->id;
    }

    public function getBook(): ?Book
    {
        return $this->book;
    }

    public function setBook(?Book $book): self
    {
        $this->book = $book;
        return $this;
    }

    public function getUserEmail(): ?string
    {
        return $this->userEmail;
    }

    public function setUserEmail(string $userEmail): self
    {
        $this->userEmail = $userEmail;
        return $this;
    }

    public function getStartDate(): ?\DateTime
    {
        return $this->startDate;
    }

    public function setStartDate(\DateTime $startDate): self
    {
        $this->startDate = $startDate;
        return $this;
    }

    public function getEndDate(): ?\DateTime
    {
        return $this->endDate;
    }

    public function setEndDate(\DateTime $endDate): self
    {
        $this->endDate = $endDate;
        return $this;
    }
}
