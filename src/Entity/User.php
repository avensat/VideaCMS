<?php

namespace App\Entity;

use FOS\UserBundle\Model\User as BaseUser;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * @ORM\Entity(repositoryClass="App\Repository\UserRepository")
 * @ORM\Table(name="users")
 */
class User extends BaseUser
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\Column(type="string", length=5, nullable=true)
     */
    private $certified;

    /**
     *
     * @Assert\Url(
     *    protocols = {"http", "https"}
     * )
     *
     * @ORM\Column(type="string", length=100, nullable=true)
     */
    private $twitter;

    /**
     *
     * @Assert\Url(
     *    protocols = {"http", "https"}
     * )
     *
     * @ORM\Column(type="string", length=100, nullable=true)
     */
    private $facebook;

    /**
     *
     * @Assert\Length(
     *      min = 2,
     *      max = 400,
     *      minMessage = "Votre bio ne peut pas faire moins de 2 caractères.",
     *      maxMessage = "Votre bio ne peut pas faire plus de 400 caractères."
     * )
     *
     * @ORM\Column(type="text", nullable=true)
     */
    private $biography;

    /**
     *
     * @Assert\Url(
     *    protocols = {"http", "https"}
     * )
     *
     * @ORM\Column(type="string", length=100, nullable=true)
     */
    private $youtube;

    /**
     * @Assert\File(maxSize="6048k")
     * @Assert\Image(mimeTypesMessage="Votre image n'est pas valide.")
     */
    protected $profilePictureFile;

    // for temporary storage
    private $tempProfilePicturePath;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    protected $profilePicturePath;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\ProviderVideo", mappedBy="user")
     */
    private $videos;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Article", mappedBy="user")
     */
    private $articles;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Poll", mappedBy="user")
     */
    private $polls;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\UploadedVideo", mappedBy="user")
     */
    private $videoUpload;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Comment", mappedBy="user")
     */
    private $comments;


    public function __construct()
    {
        parent::__construct();
        $this->videos = new ArrayCollection();
        $this->comments = new ArrayCollection();
    }

    /**
     * Set certified
     *
     * @param string $certified
     *
     * @return string
     */
    public function setCertified($certified)
    {
        return $this->certified = $certified;
    }

    /**
     * Get certified
     *
     * @return string
     */
    public function getCertified()
    {
        return $this->certified;
    }


    /**
     * Sets the file used for profile picture uploads
     *
     * @param UploadedFile $file
     * @return object
     */
    public function setProfilePictureFile(UploadedFile $file = null) {
        // set the value of the holder
        $this->profilePictureFile       =   $file;
        // check if we have an old image path
        if (isset($this->profilePicturePath)) {
            // store the old name to delete after the update
            $this->tempProfilePicturePath = $this->profilePicturePath;
            $this->profilePicturePath = null;
        } else {
            $this->profilePicturePath = 'initial';
        }

        return $this;
    }

    /**
     * Get the file used for profile picture uploads
     *
     * @return UploadedFile
     */
    public function getProfilePictureFile() {

        return $this->profilePictureFile;
    }

    /**
     * Set profilePicturePath
     *
     * @param string $profilePicturePath
     * @return User
     */
    public function setProfilePicturePath($profilePicturePath)
    {
        $this->profilePicturePath = $profilePicturePath;

        return $this;
    }

    /**
     * Get profilePicturePath
     *
     * @return string
     */
    public function getProfilePicturePath()
    {
        return $this->profilePicturePath;
    }

    /**
     * Get the absolute path of the profilePicturePath
     */
    public function getProfilePictureAbsolutePath() {
        return null === $this->profilePicturePath
            ? null
            : $this->getUploadRootDir().'/'.$this->profilePicturePath;
    }

    /**
     * Get root directory for file uploads
     *
     * @return string
     */
    protected function getUploadRootDir($type='profilePicture') {
        // the absolute directory path where uploaded
        // documents should be saved
        return __DIR__.'../../../public/'.$this->getUploadDir($type);
    }

    /**
     * Specifies where in the /web directory profile pic uploads are stored
     *
     * @return string
     */
    protected function getUploadDir($type='profilePicture') {
        // the type param is to change these methods at a later date for more file uploads
        // get rid of the __DIR__ so it doesn't screw up
        // when displaying uploaded doc/image in the view.
        return 'uploads/user/profilepics';
    }

    /**
     * Get the web path for the user
     *
     * @return string
     */
    public function getWebProfilePicturePath() {

        return '/'.$this->getUploadDir().'/'.$this->getProfilePicturePath();
    }

    /**
     * @ORM\PrePersist()
     * @ORM\PreUpdate()
     */
    public function preUploadProfilePicture() {
        if (null !== $this->getProfilePictureFile()) {
            // a file was uploaded
            // generate a unique filename
            $filename = $this->generateRandomProfilePictureFilename();
            $this->setProfilePicturePath($filename.'.'.$this->getProfilePictureFile()->guessExtension());
        }
    }

    /**
     * Generates a 32 char long random filename
     *
     * @return string
     */
    public function generateRandomProfilePictureFilename() {
        $count = 0;
        do {
            $random = random_bytes(32);
            $randomString = bin2hex($random);
            $count++;
        }
        while(file_exists($this->getUploadRootDir().'/'.$randomString.'.'.$this->getProfilePictureFile()->guessExtension()) && $count < 50);

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
    public function uploadProfilePicture() {
        // check there is a profile pic to upload
        if ($this->getProfilePictureFile() === null) {
            return;
        }
        // if there is an error when moving the file, an exception will
        // be automatically thrown by move(). This will properly prevent
        // the entity from being persisted to the database on error
        $this->getProfilePictureFile()->move($this->getUploadRootDir(), $this->getProfilePicturePath());

        // check if we have an old image
        if (isset($this->tempProfilePicturePath) && file_exists($this->getUploadRootDir().'/'.$this->tempProfilePicturePath)) {
            // delete the old image
            unlink($this->getUploadRootDir().'/'.$this->tempProfilePicturePath);
            // clear the temp image path
            $this->tempProfilePicturePath = null;
        }
        $this->profilePictureFile = null;
    }

    /**
     * @ORM\PostRemove()
     */
    public function removeProfilePictureFile()
    {
        if ($file = $this->getProfilePictureAbsolutePath() && file_exists($this->getProfilePictureAbsolutePath())) {
            unlink($file);
        }
    }


    /**
     * Set twitter.
     *
     * @param string|null $twitter
     *
     * @return User
     */
    public function setTwitter($twitter = null)
    {
        $this->twitter = $twitter;

        return $this;
    }

    /**
     * Get twitter.
     *
     * @return string|null
     */
    public function getTwitter()
    {
        return $this->twitter;
    }

    /**
     * Set facebook.
     *
     * @param string|null $facebook
     *
     * @return User
     */
    public function setFacebook($facebook = null)
    {
        $this->facebook = $facebook;

        return $this;
    }

    /**
     * Get facebook.
     *
     * @return string|null
     */
    public function getFacebook()
    {
        return $this->facebook;
    }

    /**
     * Set youtube.
     *
     * @param string|null $youtube
     *
     * @return User
     */
    public function setYoutube($youtube = null)
    {
        $this->youtube = $youtube;

        return $this;
    }

    /**
     * Get youtube.
     *
     * @return string|null
     */
    public function getYoutube()
    {
        return $this->youtube;
    }

    /**
     * Add video.
     *
     * @param \App\Entity\ProviderVideo $video
     *
     * @return User
     */
    public function addVideo(\App\Entity\ProviderVideo $video)
    {
        $this->videos[] = $video;

        return $this;
    }

    /**
     * Remove video.
     *
     * @param \App\Entity\ProviderVideo $video
     *
     * @return boolean TRUE if this collection contained the specified element, FALSE otherwise.
     */
    public function removeVideo(\App\Entity\ProviderVideo $video)
    {
        return $this->videos->removeElement($video);
    }

    /**
     * Get videos.
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getVideos()
    {
        return $this->videos;
    }

    /**
     * Add videoUpload.
     *
     * @param \App\Entity\UploadedVideo $videoUpload
     *
     * @return User
     */
    public function addVideoUpload(\App\Entity\UploadedVideo $videoUpload)
    {
        $this->videoUpload[] = $videoUpload;

        return $this;
    }

    /**
     * Remove videoUpload.
     *
     * @param \App\Entity\UploadedVideo $videoUpload
     *
     * @return boolean TRUE if this collection contained the specified element, FALSE otherwise.
     */
    public function removeVideoUpload(\App\Entity\UploadedVideo $videoUpload)
    {
        return $this->videoUpload->removeElement($videoUpload);
    }

    /**
     * Get videoUpload.
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getVideoUpload()
    {
        return $this->videoUpload;
    }

    /**
     * Add article.
     *
     * @param \App\Entity\Article $article
     *
     * @return User
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
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getArticles()
    {
        return $this->articles;
    }

    /**
     * Add comment.
     *
     *
     * @param Comment $comment
     * @return User
     */
    public function addComment(Comment $comment)
    {
        $this->comments[] = $comment;

        return $this;
    }

    /**
     * Remove comment.
     *
     *
     * @param Comment $comment
     * @return boolean TRUE if this collection contained the specified element, FALSE otherwise.
     */
    public function removeComment(Comment $comment)
    {
        return $this->comments->removeElement($comment);
    }

    /**
     * Get comments.
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getComments()
    {
        return $this->comments;
    }

    /**
     * @return mixed
     */
    public function getPools()
    {
        return $this->pools;
    }

    /**
     * @param mixed $pools
     */
    public function setPools($pools)
    {
        $this->pools = $pools;
    }

    /**
     * @return mixed
     */
    public function getBiography()
    {
        return $this->biography;
    }

    /**
     * @param mixed $biography
     */
    public function setBiography($biography)
    {
        $this->biography = $biography;
    }
}
