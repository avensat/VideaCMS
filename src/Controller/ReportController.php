<?php

namespace App\Controller;

use App\Entity\Report;
use App\Entity\User;
use App\Service\TemplateService;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class ReportController extends Controller
{

    private $template;

    public function __construct()
    {
        $template = new TemplateService();
        $this->template = $template->getTemplate();
    }

    /**
     * @Route("/report/post", name="report_post", methods="POST")
     */
    public function doPost(Request $request){
        $form = $this->createFormBuilder()
            ->add("reason", TextareaType::class)
            ->add("url", UrlType::class)
            ->getForm()
            ->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()){
            $report = new Report;
            // todo: Implement stuff here with no-flooding security
        }
    }

    // Rendered controller, no routing needed
    public function report(){
        $form = $this->createFormBuilder()
            ->add("reason", TextareaType::class)
            ->add("url", UrlType::class)
            ->setAction($this->generateUrl("report_post"))
            ->getForm();
        return $this->render($this->template.'/front/report/report.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
