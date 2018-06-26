<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Routing\Annotation\Route;

class FrontController extends Controller
{
	/**
     * @Route("/", name="front_homepage")
     */
    public function indexAction()
    {
        return $this->render('front/index.html.twig');
    }
}
