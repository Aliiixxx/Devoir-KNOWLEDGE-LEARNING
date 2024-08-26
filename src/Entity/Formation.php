<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use App\Repository\FormationRepository;

#[ORM\Entity(repositoryClass: FormationRepository::class)]
class Formation
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $title = null;

    #[ORM\OneToMany(targetEntity: Cursus::class, mappedBy: 'formation', orphanRemoval: true)]
    private Collection $cursuses;

    public function __construct()
    {
        $this->cursuses = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): static
    {
        $this->title = $title;

        return $this;
    }

    public function getCursuses(): Collection
    {
        return $this->cursuses;
    }

    public function addCursus(Cursus $cursus): static
    {
        if (!$this->cursuses->contains($cursus)) {
            $this->cursuses->add($cursus);
            $cursus->setFormation($this);
        }

        return $this;
    }

    public function removeCursus(Cursus $cursus): static
    {
        if ($this->cursuses->removeElement($cursus)) {
            if ($cursus->getFormation() === $this) {
                $cursus->setFormation(null);
            }
        }

        return $this;
    }

    public function hasUserCompleted(User $user): bool
    {
        $allLessons = new ArrayCollection();
        foreach ($this->cursuses as $cursus) {
            foreach ($cursus->getLecons() as $lecon) {
                $allLessons->add($lecon);
            }
        }

        foreach ($allLessons as $lecon) {
            if (!$lecon->isRead()) {
                return false;
            }
        }

        return true;
    }
}
