<?php

namespace App\Entity;

use App\Repository\EmployeeRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

#[ORM\Entity(repositoryClass: EmployeeRepository::class)]
class Employee
{
    #[ORM\Id]
    #[ORM\Column(type: "uuid", unique: true)]
    private UuidInterface $id;

    #[ORM\Column(length: 100)]
    private ?string $firstName = null;

    #[ORM\Column(length: 100)]
    private ?string $lastName = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    /**
     * @var Collection<int, WorkTimeEntry>
     */
    #[ORM\OneToMany(targetEntity: WorkTimeEntry::class, mappedBy: 'Employee')]
    private Collection $workTimeEntries;

    public function __construct()
    {
        $this->id = Uuid::uuid4();
        $this->workTimeEntries = new ArrayCollection();
    }

    public function getUuid(): ?string
    {
        return $this->id;
    }

    public function setUuid(UuidInterface $uuid): static
    {
        $this->id = $uuid;

        return $this;
    }

    public function getFirstName(): ?string
    {
        return $this->firstName;
    }

    public function setFirstName(string $firstName): static
    {
        $this->firstName = $firstName;

        return $this;
    }

    public function getLastName(): ?string
    {
        return $this->lastName;
    }

    public function setLastName(string $lastName): static
    {
        $this->lastName = $lastName;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): static
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    /**
     * @return Collection<int, WorkTimeEntry>
     */
    public function getWorkTimeEntries(): Collection
    {
        return $this->workTimeEntries;
    }

    public function addWorkTimeEntry(WorkTimeEntry $workTimeEntry): static
    {
        if (!$this->workTimeEntries->contains($workTimeEntry)) {
            $this->workTimeEntries->add($workTimeEntry);
            $workTimeEntry->setEmployee($this);
        }

        return $this;
    }

    public function removeWorkTimeEntry(WorkTimeEntry $workTimeEntry): static
    {
        if ($this->workTimeEntries->removeElement($workTimeEntry)) {
            // set the owning side to null (unless already changed)
            if ($workTimeEntry->getEmployee() === $this) {
                $workTimeEntry->setEmployee(null);
            }
        }

        return $this;
    }
}
