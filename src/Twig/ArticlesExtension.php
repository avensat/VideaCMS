<?php

namespace App\Twig;

use App\Entity\Article;
use Doctrine\ORM\EntityManager;

class ArticlesExtension extends \Twig_Extension
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
			new \Twig_SimpleFunction('getLastArticles', array($this, 'getLastArticles')),
		);
	}

	public function getLastArticles($limit = 5){
        $articles = $this->em->getRepository(Article::class)->findBy([], ['id' => 'DESC'],$limit,0);
		return $articles;
	}
}