<?php

namespace App\Twig;

use App\Entity\Comment;
use App\Entity\User;
use Doctrine\ORM\EntityManager;

/**
 * Class CommentExtension
 * @package App\Twig
 */
class CommentExtension extends \Twig_Extension
{
    /**
     * @var EntityManager
     */
    protected $em;

    /**
     * GetProvinceExtension constructor.
     * @param EntityManager $em
     */
    public function __construct(EntityManager $em) {
        $this->em = $em;
    }


    /**
     * @return array|\Twig_Function[]
     */
    public function getFunctions()
    {
        return array(
            new \Twig_SimpleFunction('getLastCommentsFor', array($this, 'getLastCommentsFor')),
        );
    }

    /**
     * Return the last comments for the selected user
     *
     * @param User $user
     * @param int $limit
     * @return array|object[]
     */
    public function getLastCommentsFor(User $user, $limit = 10){
        $comments = $this->em->getRepository(Comment::class)->findBy(['user' => $user], ['id' => 'DESC'], $limit);
        return $comments;
    }

}