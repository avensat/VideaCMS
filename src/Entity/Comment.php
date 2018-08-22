<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Comment
 *
 * @ORM\Table(name="comment")
 * @ORM\Entity(repositoryClass="App\Repository\CommentRepository")
 */
class Comment
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     *
     * @Assert\Length(
     *      min = 2,
     *      max = 200,
     *      minMessage = "Le commentaire doit faire plus de 2 caractères.",
     *      maxMessage = "Le commentaire ne doit pas faire plus de 200 caractères."
     * )
     *
     * @ORM\Column(name="content", type="string", length=255)
     */
    private $content;

    /**
     * @var Assert\DateTime $date
     *
     * @ORM\Column(name="date", type="datetime")
     */
    private $date;

    /**
     * @var string
     *
     * @ORM\Column(name="identifier", type="string", length=100)
     */
    private $identifier;

    /**
     * @var int
     *
     * @ORM\Column(name="likes", type="integer")
     */
    private $likes = 0;

    /**
     * @var array
     *
     * @ORM\Column(name="user_likes", type="array")
     */
    private $userLikes;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="comments")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id")
     */
    private $user;

    public function __construct()
    {
        $this->date = new \DateTime();
    }


    /**
     * Get id.
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set content.
     *
     * @param string $content
     *
     * @return Comment
     */
    public function setContent($content)
    {
        $this->content = $content;

        return $this;
    }

    /**
     * Get content.
     *
     * @return string
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * Set date.
     *
     * @param \DateTime $date
     *
     * @return Comment
     */
    public function setDate($date)
    {
        $this->date = $date;

        return $this;
    }

    /**
     * Get date.
     *
     * @return \DateTime
     */
    public function getDate()
    {
        return $this->date;
    }

    /**
     * Set identifier.
     *
     * @param string $identifier
     *
     * @return Comment
     */
    public function setIdentifier($identifier)
    {
        $this->identifier = $identifier;

        return $this;
    }

    /**
     * Get identifier.
     *
     * @return string
     */
    public function getIdentifier()
    {
        return $this->identifier;
    }

    /**
     * Set user.
     *
     * @param \App\Entity\User|null $user
     *
     * @return Comment
     */
    public function setUser(\App\Entity\User $user = null)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * Get user.
     *
     * @return \App\Entity\User|null
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * Set likes.
     *
     * @param int $likes
     *
     * @return Comment
     */
    public function setLikes($likes)
    {
        $this->likes = $likes;

        return $this;
    }

    /**
     * Get likes.
     *
     * @return int
     */
    public function getLikes()
    {
        return $this->likes;
    }


    /**
     * Set userLikes.
     *
     * @param array $userLikes
     *
     * @return Comment
     */
    public function setUserLikes($userLikes)
    {
        $this->userLikes = $userLikes;

        return $this;
    }

    /**
     * Get userLikes.
     *
     * @return array
     */
    public function getUserLikes()
    {
        return $this->userLikes;
    }
}
