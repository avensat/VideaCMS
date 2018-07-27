<?php

namespace App\Controller;

use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

/**
 * @Route("/backoffice")
 */

class BackofficeController extends Controller
{
    /**
     * @Route("/", name="backoffice_index")
     */
    public function index()
    {
        return $this->render('backoffice/index.html.twig', [
            'controller_name' => 'BackofficeController',
        ]);
    }
}
