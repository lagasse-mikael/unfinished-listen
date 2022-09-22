<?php

namespace App\Entity;

use App\Repository\FriendshipRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=FriendshipRepository::class)
 */
class Friendship
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="integer")
     */
    private $first_user_id;

    /**
     * @ORM\Column(type="integer")
     */
    private $second_friend_id;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getFirstUserId(): ?int
    {
        return $this->first_user_id;
    }

    public function setFirstUserId(int $first_user_id): self
    {
        $this->first_user_id = $first_user_id;

        return $this;
    }

    public function getSecondFriendId(): ?int
    {
        return $this->second_friend_id;
    }

    public function setSecondFriendId(int $second_friend_id): self
    {
        $this->second_friend_id = $second_friend_id;

        return $this;
    }
}
