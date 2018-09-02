<?php

namespace App\Twig;

use App\Entity\ForumRank;
use App\Entity\Message;
use App\Entity\Thread;
use App\Entity\User;
use Doctrine\ORM\EntityManager;

/**
 * Class ForumExtension
 * @package App\Twig
 */
class ForumExtension extends \Twig_Extension
{
    /**
     * @var EntityManager
     */
    protected $em;

    /**
     * GetProvinceExtension constructor.
     * @param EntityManager $em
     */
    public function __construct(EntityManager $em)
    {
        $this->em = $em;
    }


    /**
     * @return array|\Twig_Function[]
     */
    public function getFunctions()
    {
        return array(
            new \Twig_SimpleFunction('getForumRank', array($this, 'getForumRank')),
            new \Twig_SimpleFunction('getLastMessagesFor', array($this, 'getLastMessagesFor')),
            new \Twig_SimpleFunction('getMessages', array($this, 'getMessages')),
            new \Twig_SimpleFunction('getLastThreadsFor', array($this, 'getLastThreadsFor')),
            new \Twig_SimpleFunction('getThreads', array($this, 'getThreads')),
        );
    }

    /**
     * Return the forum rank for selected user
     *
     * @param User $user
     * @return string
     */
    public function getForumRank(User $user){
        $ranks = $this->em->getRepository(ForumRank::class)->findBy(['enabled' => true]);
        $posts = $this->em->getRepository(Message::class)->findBy(['user' => $user]);
        $postsNb = count($posts);

        foreach ($ranks as $rank) {
            if($postsNb >= $rank->getMsg())
                $uRank = $rank;
        }

        if(!isset($uRank))
            $uRank = "Undefined";

        return $uRank;
    }

    /**
     * Return last forum messages for selected user
     *
     * @param User $user
     * @param int $limit
     * @return array|object[]
     */
    public function getLastMessagesFor(User $user, $limit = 10){
        $posts = $this->em->getRepository(Message::class)->findBy(['user' => $user], ["id" => "DESC"], $limit);
        return $posts;
    }

    /**
     * Return last forum threads for selected user
     *
     * @param User $user
     * @param int $limit
     * @return array|object[]
     */
    public function getLastThreadsFor(User $user, $limit = 10){
        $threads = $this->em->getRepository(Thread::class)->findBy(['user' => $user], ["id" => "DESC"], $limit);
        return $threads;
    }

    /**
     * Return the number of forum messages for selected user
     *
     * @param User $user
     * @return int
     */
    public function getMessages(User $user){
        $posts = $this->em->getRepository(Message::class)->findBy(['user' => $user]);
        return count($posts);
    }

    /**
     * Return the number of forum threads for selected user
     *
     * @param User $user
     * @return int
     */
    public function getThreads(User $user){
        $threads = $this->em->getRepository(Thread::class)->findBy(['user' => $user]);
        return count($threads);
    }


}