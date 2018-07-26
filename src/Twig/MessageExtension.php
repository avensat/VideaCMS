<?php

namespace App\Twig;

use App\Entity\Message;
use Doctrine\ORM\EntityManager;

class MessageExtension extends \Twig_Extension
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


    public function getFunctions()
    {
        return array(
            new \Twig_SimpleFunction('getLastMessages', [$this, 'getLastMessages']),
        );
    }

    public function getLastMessages($limit = 10){
        $articles = $this->em->getRepository(Message::class)->findBy([], ['id' => 'DESC'],$limit,0);
        return $articles;
    }
}