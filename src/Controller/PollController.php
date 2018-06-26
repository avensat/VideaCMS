<?php

namespace App\Controller;

use App\Entity\Poll;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
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
        dump($poll);
        return $this->render('/front/poll/get.html.twig', [
            'poll' => $poll,
            'userHasRespond' => false,
        ]);
    }

    /**
     * @Route("/answer/{id}", name="answerPoll")
     * @param Poll $poll
     */
    public function answerPollAction(Poll $poll)
    {
        die();
    }
}