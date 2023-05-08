<?php

namespace App\Entity;

use App\Repository\SiteRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass=SiteRepository::class)
 * @UniqueEntity(fields={"nom"})
 */
class Site
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=50)
     * @Assert\Length(max=50, maxMessage="{{ limit }} caractères maxi")
     * @Assert\NotBlank()
     */
    private $nom;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Sortie", mappedBy="site")
     */
    private $sorties;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Participant", mappedBy="site")
     */
    private $participants;

    public function __construct()
    {
        $this->participants = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNom(): ?string
    {
        return $this->nom;
    }

    public function setNom(string $nom): self
    {
        $this->nom = $nom;

        return $this;
    }

    /**
     * @return Collection|Sortie[]
     */
    public function getSorties(): Collection
    {
        return $this->sorties;
    }

    public function addSortie(Sortie $sortie): self
    {
        if (!$this->sorties->contains($sortie)) {
            $this->sorties[] = $sortie;
            $sortie->setSite($this);
        }

        return $this;
    }

    public function removeSortie(Sortie $sortie): self
    {
        if ($this->sorties->contains($sortie)) {
            $this->sorties->removeElement($sortie);
            // set the owning side to null (unless already changed)
            if ($sortie->getSite() === $this) {
                $sortie->setSite(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Participant[]
     */
    public function getParticipants(): Collection
    {
        return $this->participants;
    }

    public function addParticipant(Participant $participant): self
    {
        if (!$this->participants->contains($participant)) {
            $this->participants[] = $participant;
            $participant->setSite($this);
        }

        return $this;
    }

    public function removeParticipant(Participant $participant): self
    {
        if ($this->participants->contains($participant)) {
            $this->participants->removeElement($participant);
            // set the owning side to null (unless already changed)
            if ($participant->getSite() === $this) {
                $participant->setSite(null);
            }
        }

        return $this;
    }

}
