<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;


/**
 * @ORM\Entity
 * @ORM\Table(name="poll")
 */
class Poll
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @Assert\Length(
     *      min = 2,
     *      max = 50,
     *      minMessage = "Le titre doit faire plus de 2 caractères.",
     *      maxMessage = "Le titre ne doit pas faire plus de 50 caractères."
     * )
     *
     * @ORM\Column(type="string", length=100, nullable=false)
     */
    private $title;

    /**
     * @Assert\Length(
     *      min = 10,
     *      max = 100,
     *      minMessage = "La question doit faire plus de 10 caractères.",
     *      maxMessage = "La question ne doit pas faire plus de 100 caractères."
     * )
     *
     * @ORM\Column(type="string", length=100, nullable=false)
     */
    private $question;

    /**
     * @var array
     * @ORM\Column(type="array", nullable=false)
     */
    private $choice;

    /**
     * @var Assert\DateTime $created_at
     *
     * @ORM\Column(name="created_at", type="datetime")
     */
    private $createdAt;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\PollAnswer", mappedBy="poll")
     */
    private $answer;


    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="polls")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id")
     */
    private $user;

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
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @param mixed $title
     */
    public function setTitle($title)
    {
        $this->title = $title;
    }

    /**
     * @return mixed
     */
    public function getQuestion()
    {
        return $this->question;
    }

    /**
     * @param mixed $question
     */
    public function setQuestion($question)
    {
        $this->question = $question;
    }

    /**
     * @return mixed
     */
    public function getChoice()
    {
        return $this->choice;
    }

    /**
     * @param mixed $choice
     */
    public function setChoice($choice)
    {
        $this->choice = $choice;
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
     */
    public function setUser($user)
    {
        $this->user = $user;
    }

    /**
     * Set answer.
     *
     * @param PollAnswer|null $answers
     *
     * @return PollAnswer
     */
    public function setAnswer(PollAnswer $answer = null)
    {
        $this->answer = $answer;

        return $this;
    }

    /**
     * Get answer.
     *
     * @return PollAnswer|null
     */
    public function getAnswer()
    {
        return $this->answer;
    }
}