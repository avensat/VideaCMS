<?php

namespace App\Controller;

use App\Entity\Poll;
use App\Entity\PollAnswer;
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
    /**
     * @Route("/", name="poll_homepage")
     */
    public function pollHomepage()
    {
        $polls = $this->getDoctrine()->getRepository(Poll::class)->findAll();
        return $this->render('/front/poll/index.html.twig', [
            'polls' => $polls,
        ]);
    }

    /**
     * @Route("/get/{id}", name="getPoll")
     * @param Poll $poll
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function getPollAction(Poll $poll)
    {
        $user = $this->getUser();

        if($user){
            $answersRepo =  $this->getDoctrine()->getRepository(PollAnswer::class);
            $answers = $answersRepo->getAnswerByPollAndUser($poll, $user);
            if(count($answers) == 0){
                $userCanRespond = true;
            } else {
                $userCanRespond = false;
            }
        } else {
            $userCanRespond = false;
        }

        return $this->render('/front/poll/get.html.twig', [
            'poll' => $poll,
            'answersCount' => count($answerRepo->getAnswerByPoll($poll)),
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
        $user = $this->get('security.token_storage')->getToken()->getUser();
        if($user != "anon."){
            $answersRepo =  $this->getDoctrine()->getRepository(PollAnswer::class);
            $answers = $answersRepo->getAnswerByPollAndUser($poll, $user);
            if(count($answers) == 0){
                $answer = new PollAnswer();
                $answer->setPoll($poll)->setUser($user)->setAnswer($request->get('choices'));
                $em = $this->getDoctrine()->getManager();
                $em->persist($answer);
                $em->flush();
                $this->addFlash('success', 'Votre réponse à bien été enregistré !');
                return $this->redirectToRoute('getPoll', ['id' => $poll->getId()]);
            } else {
                $this->addFlash('error', 'Vous avez déja repondus au sondage !');
                return $this->redirectToRoute('getPoll', ['id' => $poll->getId()]);
            }
        } else {
            $this->addFlash('error', 'Vous devez être connecté pour repondre au sondage !');
            return $this->redirectToRoute('getPoll', ['id' => $poll->getId()]);
        }
    }

    /**
     * @Route("/create", name="getPoll")
     */
    public function createAction(Request $request){

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

        return $this->render('/front/poll/create.html.twig', [
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/manage", name="poll_manage")
     */
    public function manageAction(){

        return $this->render('/front/poll/manage.html.twig', [
        ]);
    }
}