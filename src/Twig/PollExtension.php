<?php

namespace App\Twig;

use App\Entity\Poll;
use App\Entity\PollAnswer;
use App\Utils\YoutubeHelper;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\EntityManager;
use App\Entity\User;
use App\Entity\UploadedVideo;
use App\Entity\ProviderVideo;

class PollExtension extends \Twig_Extension
{
	/**
     * @var EntityManager
     */
    protected $em;

    /**
     * GetProvinceExtension constructor.
     * @param EntityManager $em
     * @param YoutubeHelper $youtubeHelper
     */
    public function __construct(EntityManager $em) {
        $this->em = $em;
    }


	public function getFunctions()
	{
		return array(
			new \Twig_SimpleFunction('getAnswers', array($this, 'getAnswers')),
		);
	}

    public function getAnswers($id)
    {
        $poll = $this->em->getRepository(Poll::class)->findOneBy(['id' => $id]);
        $answersRepo =  $this->em->getRepository(PollAnswer::class);
        $answers = $answersRepo->getAnswerByPoll($poll);
        return $answerss;
    }

}