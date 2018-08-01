<?php

namespace App\Controller;

use App\Service\TemplateService;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Routing\Annotation\Route;

class FrontController extends Controller
{

    private $template;

    public function __construct()
    {
        $template = new TemplateService();
        $this->template = $template->getTemplate();
    }

    /**
     * @Route("/", name="front_homepage")
     */
    public function indexAction()
    {
        return $this->render($this->template.'/front/index.html.twig');
    }
}
