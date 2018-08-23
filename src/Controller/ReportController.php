<?php

namespace App\Controller;

use App\Entity\Report;
use App\Entity\User;
use App\Service\TemplateService;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
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
            ->add("entity", HiddenType::class)
            ->add("identifier", HiddenType::class)
            ->getForm()
            ->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()){
            $data = $form->getData();
            $check = $this->getDoctrine()->getRepository(Report::class)->findOneBy(['identifier' => $data['identifier'], "user" => $this->getUser()]);
            if($check){
                $this->addFlash('error', "Vous avez déjà signalé ce contenu.");
            }
            else{
                $report = new Report;
                $report->setUser($this->getUser());
                $report->setReason($data['reason']);
                $report->setEntity($data['entity']);
                $report->setIdentifier($data['identifier']);
                $report->setUrl($data['url']);
                $em = $this->getDoctrine()->getManager();
                $em->persist($report);
                $em->flush();
                $this->addFlash('success', "Votre signalement a bien été envoyé.");
            }
            return $this->redirect($data['url']);
        }
    }

    // Rendered controller, no routing needed
    public function report($identifier, $entity, $url){
        $form = $this->createFormBuilder()
            ->add("reason", TextareaType::class)
            ->add("url", UrlType::class)
            ->add("entity", HiddenType::class)
            ->add("identifier", HiddenType::class)
            ->setAction($this->generateUrl("report_post"))
            ->getForm();
        return $this->render($this->template.'/front/report/report.html.twig', [
            'form' => $form->createView(),
            'identifier' => $identifier,
            'entity' => $entity,
            'url' => $url
        ]);
    }
}
