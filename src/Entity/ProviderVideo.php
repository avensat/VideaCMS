<?php

namespace App\Entity;

use App\Utils\YoutubeHelper;
use DateTime;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity
 * @ORM\Table(name="provider_videos")
 */
class ProviderVideo
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
     * @ORM\Column(type="string", length=100, nullable=false)
     */
    private $title;

    /**
     * @Assert\Url(
     *    protocols = {"http", "https"}
     * )
     * @ORM\Column(type="string", length=200, nullable=false)
     */
    private $url;

    /**
     * @ORM\Column(type="string", length=200, nullable=false)
     */
    private $provider;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="videos")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id")
     */
    private $user;

    /**
     * @var DateTime $createdAt
     *
     * @ORM\Column(name="created_at", type="datetime")
     */
    private $createdAt;

    public function __construct()
    {
        $this->createdAt = new \DateTime("now");
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
     * @return ProviderVideo
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
     * Set url.
     *
     * @param string $url
     *
     * @return ProviderVideo
     */
    public function setUrl($url)
    {
        $this->url = $url;

        return $this;
    }

    /**
     * Get url.
     *
     * @return string
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * Set provider.
     *
     * @param string $provider
     *
     * @return ProviderVideo
     */
    public function setProvider($provider)
    {
        $this->provider = $provider;

        return $this;
    }

    /**
     * Get provider.
     *
     * @return string
     */
    public function getProvider()
    {
        return $this->provider;
    }

    /**
     * Set user.
     *
     * @param integer $user
     *
     * @return ProviderVideo
     */
    public function setUser($user)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * Get user.
     *
     * @return integer
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * @return ?DateTime
     */
    public function getCreatedAt(): ?DateTime
    {
        return $this->createdAt;
    }

    /**
     * @param DateTime $createdAt
     */
    public function setCreatedAt(DateTime $createdAt)
    {
        $this->createdAt = $createdAt;
    }

    public function getUrlId(){
        $parts = parse_url($this->url);
        parse_str($parts['query'], $query);
        return $query['v'];
    }

    public function getThumbnail(){
        $url = "https://img.youtube.com/vi/" . $this->getUrlId() . "/maxresdefault.jpg";
        return $url;
    }
}
