<?php

namespace App\Controller;

use App\Entity\Poll;
use App\Utils\YoutubeHelper;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/test")
 */
class TestController extends Controller
{
    /**
     * @Route("/poll", name="polltest")
     */
    public function pollTestAction()
    {
        $poll = new Poll();
        $poll->setTitle('Pain chocolat / chocolatine');
        $poll->setQuestion('Pain au chocolat ou chocolatine');
        $poll->setChoice([
            ['id' => 1, 'value' => 'Pain au chocolat'],
            ['id' => 2, 'value' => 'chocolatine'],
        ]);

        $em = $this->getDoctrine()->getManager();
        $em->persist($poll);
        $em->flush();

        return true;
    }

    /**
     * @Route("/get/poll/{id}", name="getpolltest")
     */
    public function getPollTestAction(Poll $poll)
    {
        dump($poll);
        die();
    }

    /**
     * @Route("/", name="test")
     * @param YoutubeHelper $youtubeHelper
     */
    public function testAction(YoutubeHelper $youtubeHelper)
    {
        $videoInfo = $youtubeHelper->getVideoInfo("37StRy0LEbI");
        dump($videoInfo);
        return $this->render('/front/index.html.twig');
    }


}
