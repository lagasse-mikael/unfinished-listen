<?php

namespace App\Entity;

use App\Repository\PostRepository;
use Doctrine\ORM\Mapping as ORM;

use App\Entity\User;

/**
 * @ORM\Entity(repositoryClass=PostRepository::class)
 */
class Post
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="postsByUser", fetch="EAGER")
     */
    public $creator;

    /**
     * @ORM\Column(type="string", length=150)
     */
    public $trackID;

    public $track;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCreator(): ?User
    {
        return $this->creator;
    }

    public function setCreator(User $_creator): self
    {
        $this->creator = $_creator;

        return $this;
    }

    public function getTrackID(): ?string
    {
        return $this->trackID;
    }

    public function setTrackID(string $trackID): self
    {
        $this->trackID = $trackID;

        return $this;
    }

    public function setLoadedTrack($_track): self
    {
        $this->track = $_track;

        return $this;
    }

    public function getLoadedTrack()
    {
        return $this->track;
    }
}
