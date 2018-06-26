<?php

namespace App\Entity;

use DateTime;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * @ORM\Entity(repositoryClass="App\Repository\UploadedVideoRepository")
 * @ORM\Table(name="uploaded_video")
 */
class UploadedVideo
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
     *      max = 500,
     *      maxMessage = "La description ne doit pas faire plus de 500 caractères."
     * )
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $description;

    /**
     * @ORM\Column(type="integer")
     */
    private $likes = 0;

    /**
     * @var array
     *
     * @ORM\Column(name="user_likes", type="array")
     */
    private $userLikes;

    /**
     * @ORM\Column(type="integer")
     */
    private $views = 0;

    /**
     * @ORM\Column(type="boolean")
     */
    private $public;

     /**
     * @Assert\File(
     *     maxSize = "500M",
     *     mimeTypes = {"video/mp4"},
     *     mimeTypesMessage = "Format vidéo inccorect",
     *     maxSizeMessage = "La taille maximum est 500 Mo."
     * )
     * @Assert\NotBlank(message="Vous devez fournir une vidéo.")
     */
    private $videoFile;

    /**
     * @var string $videoPath
     *
     * @ORM\Column(name="videoPath", type="string", length=255, nullable=true)
     */
    private $videoPath;

    // for temporary storage
    private $tempVideoPath;


    /**
     * @var DateTime $createdAt
     *
     * @ORM\Column(name="created_at", type="datetime")
     */
    private $createdAt;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="videoUpload")
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

    // UploadedVideo functions

    public function __construct()
    {
        $this->createdAt = new \DateTime("now");
    }

    /**
     * Sets the file used for profile picture uploads
     *
     * @param UploadedFile $file
     * @return object
     */
    public function setVideoFile(UploadedFile $file = null) {
        // set the value of the holder
        $this->videoFile       =   $file;
        // check if we have an old image path
        if (isset($this->VideoPath)) {
            // store the old name to delete after the update
            $this->tempVideoPath = $this->VideoPath;
            $this->VideoPath = null;
        } else {
            $this->VideoPath = 'initial';
        }

        return $this;
    }

    /**
     * Get the absolute path of the videoPath
     * With support for Windows server (forgive me god...)
     */
    public function getVideoAbsolutePath() {
            return null === $this->videoPath
                ? null
                : $this->getUploadRootDir().'/'.$this->videoPath;

    }

    /**
     * Get root directory for file uploads
     *
     * @return string
     */
    protected function getUploadRootDir($type='video') {
        // the absolute directory path where uploaded
        // documents should be saved
        return __DIR__.'/../../public/'.$this->getUploadDir($type);
    }

    /**
     * Specifies where in the /web directory profile pic uploads are stored
     *
     * @return string
     */
    protected function getUploadDir($type='video') {
        // the type param is to change these methods at a later date for more file uploads
        // get rid of the __DIR__ so it doesn't screw up
        // when displaying uploaded doc/image in the view.
        return 'uploads/videos';
    }

    /**
     * Get the web path for the user
     *
     * @return string
     */
    public function getWebVideoPath() {

        return '/'.$this->getUploadDir().'/'.$this->getVideoPath();
    }

    /**
     * @ORM\PrePersist()
     * @ORM\PreUpdate()
     */
    public function preUploadVideo() {
        if (null !== $this->getVideoFile()) {
            // a file was uploaded
            // generate a unique filename
            $filename = $this->generateRandomVideoFilename();
            $this->setVideoPath($filename.'.'.$this->getVideoFile()->guessExtension());
        }
    }

    /**
     * Generates a 32 char long random filename
     *
     * @return string
     */
    public function generateRandomVideoFilename() {
        $count = 0;
        do {
            $random = random_bytes(32);
            $randomString = bin2hex($random);
            $count++;
        }
        while(file_exists($this->getUploadRootDir().'/'.$randomString.'.'.$this->getVideoFile()->guessExtension()) && $count < 50);

        return $randomString;
    }

    /**
     * @ORM\PostPersist()
     * @ORM\PostUpdate()
     *
     * UploadedVideo the profile picture
     *
     * @return mixed
     */
    public function uploadVideo() {
        // check there is a profile pic to upload
        if ($this->getVideoFile() === null) {
            return;
        }
        // if there is an error when moving the file, an exception will
        // be automatically thrown by move(). This will properly prevent
        // the entity from being persisted to the database on error
        $this->getVideoFile()->move($this->getUploadRootDir(), $this->getVideoPath());

        // check if we have an old image
        if (isset($this->tempVideoPath) && file_exists($this->getUploadRootDir().'/'.$this->tempVideoPath)) {
            // delete the old image
            unlink($this->getUploadRootDir().'/'.$this->tempVideoPath);
            // clear the temp image path
            $this->tempVideoPath = null;
        }
        $this->videoFile = null;
    }

    /**
     * @ORM\PostRemove()
     */
    public function removeVideoFile()
    {
        if ($file = $this->getVideoAbsolutePath() && file_exists($this->getVideoAbsolutePath())) {
            unlink($this->getVideoAbsolutePath());
        }
    }

    // Normal functions

    public function getVideo()
    {
        return $this->videoFile;
    }

    public function setVideo($video)
    {
        $this->videoFile = $video;

        return $this;
    }

    /**
     * Set title.
     *
     * @param string $title
     *
     * @return UploadedVideo
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
     * Set likes.
     *
     * @param int $likes
     *
     * @return UploadedVideo
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
     * Set views.
     *
     * @param int $views
     *
     * @return UploadedVideo
     */
    public function setViews($views)
    {
        $this->views = $views;

        return $this;
    }

    /**
     * Get views.
     *
     * @return int
     */
    public function getViews()
    {
        return $this->views;
    }

    /**
     * Set user.
     *
     * @param \App\Entity\User|null $user
     *
     * @return UploadedVideo
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
     * Set path.
     *
     * @param string|null $path
     *
     * @return UploadedVideo
     */
    public function setPath($path = null)
    {
        $this->path = $path;

        return $this;
    }

    /**
     * Get path.
     *
     * @return string|null
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * Set createdAt.
     *
     * @param DateTime $createdAt
     *
     * @return UploadedVideo
     */
    public function setCreatedAt($createdAt)
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    /**
     * Get createdAt.
     *
     * @return DateTime
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * Get videoFile.
     *
     * @return string
     */
    public function getVideoFile()
    {
        return $this->videoFile;
    }

    /**
     * Set videoPath.
     *
     * @param string|null $videoPath
     *
     * @return UploadedVideo
     */
    public function setVideoPath($videoPath = null)
    {
        $this->videoPath = $videoPath;

        return $this;
    }

    /**
     * Get videoPath.
     *
     * @return string|null
     */
    public function getVideoPath()
    {
        return $this->videoPath;
    }

    /**
     * Set public.
     *
     * @param int $public
     *
     * @return UploadedVideo
     */
    public function setPublic($public)
    {
        $this->public = $public;

        return $this;
    }

    /**
     * Get public.
     *
     * @return int
     */
    public function getPublic()
    {
        return $this->public;
    }

    /**
     * Set userLikes.
     *
     * @param array $userLikes
     *
     * @return UploadedVideo
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

    /**
     * Set description.
     *
     * @param string|null $description
     *
     * @return UploadedVideo
     */
    public function setDescription($description = null)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Get description.
     *
     * @return string|null
     */
    public function getDescription()
    {
        return $this->description;
    }
}
