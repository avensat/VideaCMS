<?php

namespace App\Twig;

use App\Entity\Article;
use App\Entity\ForumRank;
use App\Entity\Message;
use App\Entity\Theme;
use App\Entity\Thread;
use App\Entity\User;
use App\Service\TemplateService;
use Doctrine\ORM\EntityManager;
use Symfony\Component\Yaml\Yaml;

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


    public function getFunctions()
    {
        return array(
            new \Twig_SimpleFunction('getForumRank', array($this, 'getForumRank')),
            new \Twig_SimpleFunction('getMessages', array($this, 'getMessages')),
            new \Twig_SimpleFunction('getThreads', array($this, 'getThreads')),
        );
    }


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



    public function getMessages(User $user){
        $posts = $this->em->getRepository(Message::class)->findBy(['user' => $user]);
        return count($posts);
    }

    public function getThreads(User $user){
        $threads = $this->em->getRepository(Thread::class)->findBy(['user' => $user]);
        return count($threads);
    }


}