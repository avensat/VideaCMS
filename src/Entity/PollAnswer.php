<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;


/**
 * @ORM\Entity
 * @ORM\Table(name="poll_answer")
 */
class PollAnswer
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id")
     */
    private $user;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Poll")
     * @ORM\JoinColumn(name="poll_id", referencedColumnName="id")
     */
    private $poll;

    /**
     * @ORM\Column(type="integer")
     */
    private $anwser;

    /**
     * @var Assert\DateTime $created_at
     *
     * @ORM\Column(name="created_at", type="datetime")
     */
    private $createdAt;

    public function __construct()
    {
        $this->createdAt = new \DateTime("now");
    }


    /**
     * @return Assert\DateTime
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * @param Assert\DateTime $createdAt
     */
    public function setCreatedAt($createdAt)
    {
        $this->createdAt = $createdAt;
    }

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return mixed
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * @param mixed $user
     * @return PollAnswer
     */
    public function setUser($user)
    {
        $this->user = $user;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getPoll()
    {
        return $this->poll;
    }

    /**
     * @param mixed $poll
     * @return PollAnswer
     */
    public function setPoll($poll)
    {
        $this->poll = $poll;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getAnwser()
    {
        return $this->anwser;
    }

    /**
     * @param mixed $anwser
     * @return PollAnswer
     */
    public function setAnwser($anwser)
    {
        $this->anwser = $anwser;
        return $this;
    }



}