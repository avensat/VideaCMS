<?php

namespace App\Twig;

use App\Entity\Article;
use App\Entity\Theme;
use App\Service\TemplateService;
use Doctrine\ORM\EntityManager;

class CoreExtension extends \Twig_Extension
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
            new \Twig_SimpleFunction('getTemplateInfo', array($this, 'getTemplateInfo')),
        );
    }

    public function getTemplateInfo($str){
        $templateService = new TemplateService();
        $template = $templateService->getTemplate();
        $obj = $this->em->getRepository(Theme::class)->findOneBy(["name" => $template]);
        $params = $obj->getParameters();
        foreach ($params as $param){
            if($param["parameter"] == $str)
                return $param["value"];
        }

        return 0;

    }

}