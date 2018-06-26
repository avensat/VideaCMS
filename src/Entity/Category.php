<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity
 * @ORM\Table(name="category")
 */
class Category
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     *
     * @Assert\Length(
     *      min = 2,
     *      max = 50,
     *      minMessage = "Le titre doit faire plus de 2 caractères.",
     *      maxMessage = "Le titre ne doit pas faire plus de 50 caractères."
     * )
     *
     * @ORM\Column(type="string", length=50, nullable=false)
     */
    private $title;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Article", mappedBy="category")
     */
    private $articles;

    public function __construct()
    {
        $this->articles = new ArrayCollection();
    }

    public function __toString()
    {
        return $this->title;
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
     * Set title.
     *
     * @param string $title
     *
     * @return Category
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
     * Add article.
     *
     * @param \App\Entity\Article $article
     *
     * @return Category
     */
    public function addArticle(\App\Entity\Article $article)
    {
        $this->articles[] = $article;

        return $this;
    }

    /**
     * Remove article.
     *
     * @param \App\Entity\Article $article
     *
     * @return boolean TRUE if this collection contained the specified element, FALSE otherwise.
     */
    public function removeArticle(\App\Entity\Article $article)
    {
        return $this->articles->removeElement($article);
    }

    /**
     * Get articles.
     *
     * @return Collection|Article[]
     */
    public function getArticles()
    {
        return $this->articles;
    }
}
