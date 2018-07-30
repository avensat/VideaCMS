<?php

namespace App\Twig;

use App\Entity\Article;
use App\Entity\User;
use Doctrine\ORM\EntityManager;

class BackofficeExtension extends \Twig_Extension
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
            new \Twig_SimpleFunction('countByDate', array($this, 'countByDate')),
        );
    }

    public function countByDate($entity, $days = null){
        if($days){
            $date = new \DateTime("now");
            $date->sub(new \DateInterval('P'.$days.'D'));
            $query = $this->em
                ->createQuery("SELECT COUNT(d) FROM App\Entity\\$entity d WHERE d.created_at > :cDate")
                ->setParameter('cDate', $date);
        }
        else{
            $query = $this->em->createQuery("SELECT COUNT(d) FROM App\Entity\\$entity d");
        }

        $data = $query->getResult();
        return $data[0][1];
    }
}