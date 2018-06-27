<?php

namespace App\Controller;

use App\Entity\Poll;
use App\Entity\PollAnswer;
use phpseclib\File\ANSI;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
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
        $user = $this->get('security.token_storage')->getToken()->getUser();
        $answerRepo =  $this->getDoctrine()->getRepository(PollAnswer::class);
        if($user != "anon."){
            $answers = $answerRepo->getAnswerByPollAndUser($poll, $user);
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
}