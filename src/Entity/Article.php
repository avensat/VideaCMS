<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="App\Repository\ArticleRepository")
 * @ORM\Table(name="articles")
 */
class Article
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
     * @ORM\Column(type="string", length=50, nullable=false)
     */
    private $title;

    /**
     * @Assert\Length(
     *      min = 10,
     *      minMessage = "Le contenu doit faire plus de 20 caractères."
     * )
     * @ORM\Column(type="text", nullable=false)
     */
    private $content;

    /**
     * @var Assert\DateTime $creation
     *
     * @ORM\Column(name="creation", type="datetime", nullable=false)
     */
    private $creation;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Category", inversedBy="articles")
     * @ORM\JoinColumn(name="category_id", referencedColumnName="id")
     */
    private $category;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="articles")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id")
     */
    private $user;

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
     * Set title.
     *
     * @param string $title
     *
     * @return Article
     */
    public function setTitle($title)
    {
        $this->title = $title;

        return $this;
    }

    /**
     * Get title.
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Set content.
     *
     * @param string $content
     *
     * @return Article
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
     * Set creation.
     *
     * @param \DateTime $author
     *
     * @return Article
     */
    public function setCreation($creation)
    {
        $this->creation = $creation;

        return $this;
    }

    /**
     * Get creation.
     *
     * @return \DateTime
     */
    public function getCreation()
    {
        return $this->creation;
    }

    public function getCategory()
    {
        return $this->category;
    }

    public function setCategory(Category $category)
    {
        $this->category = $category;
    }



    /**
     * Set user.
     *
     * @param \App\Entity\User|null $user
     *
     * @return Article
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
}
