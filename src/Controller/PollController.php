<?php

namespace App\Controller;

use App\Entity\Poll;
use App\Entity\PollAnswer;
use App\Service\TemplateService;
use phpseclib\File\ANSI;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;


/**
 * @Route("/poll")
 */
class PollController extends Controller
{
    private $template;

    public function __construct()
    {
        $template = new TemplateService();
        $this->template = $template->getTemplate();
    }

    /**
     * @Route("/", name="poll_homepage")
     */
    public function pollHomepage(Request $request)
    {

        $em    = $this->get('doctrine.orm.entity_manager');
        $dql   = "SELECT a FROM App\Entity\Poll a";
        $query = $em->createQuery($dql);

        $paginator  = $this->get('knp_paginator');
        $polls = $paginator->paginate(
            $query, /* query NOT result */
            $request->query->getInt('page', 1)/*page number*/,
            10/*limit per page*/
        );

        return $this->render($this->template.'/front/poll/index.html.twig', [
            'polls' => $polls,
        ]);
    }

    /**
     * @Route("/v/{id}", name="poll_view")
     * @param Poll $poll
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function getPollAction(Poll $poll)
    {
        $user = $this->getUser();

        if($user){
            $answersRepo = $this->getDoctrine()->getRepository(PollAnswer::class);
            $answers = $answersRepo->getAnswerByPollAndUser($poll, $user);
            if(count($answers) == 0){
                $userCanRespond = true;
            } else {
                $userCanRespond = false;
            }
        } else {
            $userCanRespond = false;
        }

        return $this->render($this->template.'/front/poll/get.html.twig', [
            'poll' => $poll,
            'answersCount' => count($answersRepo->getAnswerByPoll($poll)),
            'userCanRespond' => $userCanRespond,
        ]);
    }

    /**
     * @Route("/answer/{id}", name="answerPoll")
     * @param Poll $poll
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function answerPollAction(Request $request, Poll $poll)
    {

        $this->denyAccessUnlessGranted('ROLE_USER');

        $user = $this->getUser();
        if($user != "anon."){
            $answersRepo =  $this->getDoctrine()->getRepository(PollAnswer::class);
            $answers = $answersRepo->getAnswerByPollAndUser($poll, $user);
            if(count($answers) == 0){
                $answer = new PollAnswer();
                $answer->setPoll($poll)->setUser($user)->setAnswer($request->get('choices'));
                $em = $this->getDoctrine()->getManager();
                $em->persist($answer);
                $em->flush();
                $this->addFlash('success', 'Votre réponse a bien été enregistrée !');
                return $this->redirectToRoute('poll_view', ['id' => $poll->getId()]);
            } else {
                $this->addFlash('error', 'Vous avez déjà répondu au sondage !');
                return $this->redirectToRoute('poll_view', ['id' => $poll->getId()]);
            }
        } else {
            $this->addFlash('error', 'Vous devez être connecté pour répondre au sondage !');
            return $this->redirectToRoute('poll_view', ['id' => $poll->getId()]);
        }
    }

    /**
     * @Route("/create", name="create_poll")
     */
    public function createAction(Request $request){

        $this->denyAccessUnlessGranted('ROLE_CREATOR');

        $form = $this->createFormBuilder()
            ->add('title', TextType::class)
            ->add('question', TextType::class)
            ->add('poll', HiddenType::class)
            ->getForm()
            ->handleRequest($request)
        ;

        if($form->isSubmitted()){
            $data = $form->getData();

            $poll = new Poll();
            $poll->setTitle($data['title']);
            $poll->setQuestion($data['question']);
            $poll->setUser($this->getUser());
            $out = [];
            foreach ($data['poll'] as $key => $value){
                $in = ["id" => $key, "value" => $value];
                array_push($out, $in);
            }
            $poll->setChoice($out);

            $em = $this->getDoctrine()->getManager();
            $em->persist($poll);
            $em->flush();
            $this->addFlash('success', 'Sondage ajouté !');
            return $this->redirectToRoute("poll_manage");
        }

        return $this->render($this->template.'/front/poll/create.html.twig', [
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/manage", name="poll_manage")
     */
    public function manageAction(){

        $this->denyAccessUnlessGranted('ROLE_CREATOR');

        $polls = $this->getDoctrine()->getRepository(Poll::class)->findBy(['user' => $this->getUser()]);
        return $this->render($this->template.'/front/poll/manage.html.twig', [
            "polls" => $polls
        ]);
    }

    /**
     * @Route("/edit/{poll}", name="poll_edit")
     */
    public function editAction(Request $request, Poll $poll){

        $this->denyAccessUnlessGranted('ROLE_CREATOR');

        if($poll->getUser() != $this->getUser())
            return $this->redirectToRoute('poll_manage');

        $choices = $poll->getChoice();

        $form = $this->createFormBuilder()
            ->add('title', TextType::class, ['data' => $poll->getTitle()])
            ->add('question', TextType::class, ['data' => $poll->getQuestion()])
            ->add('poll', HiddenType::class)
            ->getForm()
            ->handleRequest($request)
        ;

        if($form->isSubmitted()){
            $data = $form->getData();

            $poll->setTitle($data['title']);
            $poll->setQuestion($data['question']);
            $out = [];
            foreach ($data['poll'] as $key => $value){
                $in = ["id" => $key, "value" => $value];
                array_push($out, $in);
            }
            $poll->setChoice($out);

            $em = $this->getDoctrine()->getManager();
            $em->persist($poll);
            $em->flush();
            $this->addFlash('success', 'Sondage modifié !');
            return $this->redirectToRoute("poll_manage");
        }


        return $this->render($this->template.'/front/poll/edit.html.twig', [
            'form' => $form->createView(),
            'choices' => $choices
        ]);
    }

    /**
     * @Route("/delete/{poll}", name="poll_remove")
     */
    public function deleteAction(Request $request, Poll $poll){
        $this->denyAccessUnlessGranted('ROLE_CREATOR');

        if($poll->getUser() != $this->getUser())
            return $this->redirectToRoute('poll_manage');

        $em = $this->getDoctrine()->getManager();
        $em->remove($poll);
        $em->flush();
        $this->addFlash('success', 'Sondage supprimé !');
        return $this->redirectToRoute("poll_manage");
    }
}