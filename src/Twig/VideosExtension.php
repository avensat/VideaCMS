<?php

namespace App\Twig;

use App\Utils\YoutubeHelper;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\EntityManager;
use App\Entity\User;
use App\Entity\UploadedVideo;
use App\Entity\ProviderVideo;

class VideosExtension extends \Twig_Extension
{
	/**
     * @var EntityManager
     */
    protected $em;

    protected $youtubeHelper;

    /**
     * GetProvinceExtension constructor.
     * @param EntityManager $em
     * @param YoutubeHelper $youtubeHelper
     */
    public function __construct(EntityManager $em, YoutubeHelper $youtubeHelper) {
        $this->em = $em;
        $this->youtubeHelper = $youtubeHelper;
    }


	public function getFunctions()
	{
		return array(
			new \Twig_SimpleFunction('getUpVideoUrl', array($this, 'getUpVideoUrl')),
			new \Twig_SimpleFunction('getExtVideoUrl', array($this, 'getExtVideoUrl')),
			new \Twig_SimpleFunction('getLastVideos', array($this, 'getLastVideos')),
			new \Twig_SimpleFunction('getLastVideoForUser', array($this, 'getLastVideoForUser')),
			new \Twig_SimpleFunction('getLastVideosForUser', array($this, 'getLastVideosForUser')),
			new \Twig_SimpleFunction('getUrlId', array($this, 'getUrlId')),
            new \Twig_SimpleFunction('getLikeCountForId', array($this, 'getLikeCountForId')),
            new \Twig_SimpleFunction('getCommentCountForId', array($this, 'getCommentCountForId')),
            new \Twig_SimpleFunction('getViewCountForId', array($this, 'getViewCountForId')),
            new \Twig_SimpleFunction('getHomeVideo', array($this, 'getHomeVideo')),
		);
	}

	public function getLastVideos($limit = 5){
        $providerVideos = $this->em->getRepository(ProviderVideo::class)->findBy([], ["id" => "DESC"], $limit, 0);
        $uploadedVideos = $this->em->getRepository(UploadedVideo::class)->findBy([], ["id" => "DESC"], $limit, 0);
        $videos = array_merge($providerVideos, $uploadedVideos);
        usort($videos, function($a, $b) {
            if ($a == $b)
                return 0;
            return $a < $b ? 1 : -1;
        });
		return $videos;
	}

    public function getLastVideosForUser(User $user){
        $providerVideos = $this->em->getRepository(ProviderVideo::class)->findBy(['user' => $user]);
        $uploadedVideos = $this->em->getRepository(UploadedVideo::class)->findBy(['user' => $user]);
        $videos = array_merge($providerVideos, $uploadedVideos);
        usort($videos, function($a, $b) {
            if ($a == $b)
                return 0;
            return $a < $b ? 1 : -1;
        });
        return $videos;
    }

	public function getLastVideoForUser(User $user){
        $providerVideo = $this->em->getRepository(ProviderVideo::class)->findOneBy(["user" => $user], ['id' => 'DESC']);
        $uploadedVideo = $this->em->getRepository(UploadedVideo::class)->findOneBy(["user" => $user], ['id' => 'DESC']);

        if(!$uploadedVideo)
            return $providerVideo;
        elseif(!$providerVideo)
            return $uploadedVideo;

        if($providerVideo->getCreatedAt() > $uploadedVideo->getCreatedAt())
            return $providerVideo;
        else
            return $uploadedVideo;
    }


    public function getUrlId(ProviderVideo $video)
    {
        return $video->getUrlId();
    }

    public function getLikeCountForId($id){
        $likeCount = $this->youtubeHelper->getVideoInfo($id);
        if ($likeCount != "Error"){
            return $likeCount->likeCount;
        } else {
            return 0;
        }
    }

    public function getViewCountForId($id){
        $likeCount = $this->youtubeHelper->getVideoInfo($id);
        if ($likeCount != "Error"){
            return $likeCount->viewCount;
        } else {
            return 0;
        }
    }

    public function getCommentCountForId($id){
        $likeCount = $this->youtubeHelper->getVideoInfo($id);
        if ($likeCount != "Error"){
            return $likeCount->commentCount;
        } else {
            return 0;
        }
    }

    public function getHomeVideo(){
        $providerVideo = $this->em->getRepository(ProviderVideo::class)->findOneBy([], ['id' => 'DESC']);
        $uploadedVideo = $this->em->getRepository(UploadedVideo::class)->findOneBy([], ['id' => 'DESC']);

        if(!$uploadedVideo)
            return $providerVideo;
        elseif(!$providerVideo)
            return $uploadedVideo;

        if($providerVideo->getCreatedAt() > $uploadedVideo->getCreatedAt())
            return $providerVideo;
        else
            return $uploadedVideo;
    }
}