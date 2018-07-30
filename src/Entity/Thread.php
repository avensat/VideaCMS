<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="App\Repository\ThreadRepository")
 */
class Thread
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="threads")
     * @ORM\JoinColumn(nullable=false)
     */
    private $user;

    /**
     * @ORM\Column(type="datetime")
     */
    private $created_at;

    /**
     * @Assert\Length(
     *      min = 2,
     *      max = 100,
     *      minMessage = "Le sujet doit faire plus de 2 caractères.",
     *      maxMessage = "Le sujet ne peut pas faire plus de 100 caractères."
     * )
     * @ORM\Column(type="string", length=100)
     */
    private $title;

    /**
     * @ORM\Column(type="string", length=50)
     */
    private $status;

    /**
     * @ORM\Column(type="boolean")
     */
    private $locked;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $last_modification;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $last_message;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Message", mappedBy="thread", orphanRemoval=true)
     */
    private $messages;

    /**
     * @Assert\NotBlank(
     *     message = "Le texte ne doit pas être vide"
     * )
     *
     * @ORM\Column(type="text")
     */
    private $content;

    public function __construct()
    {
        $this->messages = new ArrayCollection();
        $this->created_at = new \DateTime("now");
        $this->last_message = new \DateTime("now");
        $this->locked = 0;
    }

    public function getId()
    {
        return $this->id;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): self
    {
        $this->user = $user;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->created_at;
    }

    public function setCreatedAt(\DateTimeInterface $created_at): self
    {
        $this->created_at = $created_at;

        return $this;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(string $status): self
    {
        $this->status = $status;

        return $this;
    }

    public function getLocked(): ?bool
    {
        return $this->locked;
    }

    public function setLocked(string $locked): self
    {
        $this->locked = $locked;

        return $this;
    }

    /**
     * @return Collection|Message[]
     */
    public function getMessages(): Collection
    {
        return $this->messages;
    }

    public function addMessage(Message $message): self
    {
        if (!$this->messages->contains($message)) {
            $this->messages[] = $message;
            $message->setThread($this);
        }

        return $this;
    }

    public function removeMessage(Message $message): self
    {
        if ($this->messages->contains($message)) {
            $this->messages->removeElement($message);
            // set the owning side to null (unless already changed)
            if ($message->getThread() === $this) {
                $message->setThread(null);
            }
        }

        return $this;
    }

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function setContent(string $content): self
    {
        $this->content = $content;

        return $this;
    }

    public function getLastModification(): ?\DateTimeInterface
    {
        return $this->last_modification;
    }

    public function setLastModification(\DateTimeInterface $last_modification): self
    {
        $this->last_modification = $last_modification;

        return $this;
    }

    public function getLastMessage(): ?\DateTimeInterface
    {
        return $this->last_message;
    }

    public function setLastMessage(\DateTimeInterface $last_message): self
    {
        $this->last_message = $last_message;

        return $this;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;

        return $this;
    }
}
