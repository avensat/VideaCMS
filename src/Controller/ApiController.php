<?php

namespace App\Controller;

use App\Entity\Poll;
use App\Entity\PollAnswer;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/api")
 */
class ApiController extends Controller
{
    /**
     * @Route("/poll/{id}", name="apiGetPoll")
     * @param Poll $poll
     */
    public function answerPollAction(Poll $poll)
    {
        $answers = $this->getDoctrine()->getRepository(PollAnswer::class)->findBy(["poll" => $poll]);
        $label = [];
        $data = [];

        foreach ($poll->getChoice() as $choice){
            array_push($label, $choice['value']);
            array_push($data, 0);
        }

        foreach ($answers as $answer){
            $data[$answer->getAnswer()]++;
        }

        $poll = [
            "label" => $label,
            "data" => $data,
        ];
        return $this->json($poll);
    }
}