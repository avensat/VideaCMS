<?php

namespace App\Repository;

use App\Entity\UploadedVideo;
use Symfony\Component\HttpFoundation\Session\Session;

/**
 * UploadedVideo
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class UploadedVideoRepository extends \Doctrine\ORM\EntityRepository
{

    public function addView($id){
        $session = new Session();

        if(!$session->has("like_timer")){
            $session->set("like_timer", array("init"));
        }

        foreach ($session->get("like_timer") as $vId){
            if($vId == $id){
                $update = false;
            }
        }

        if(!isset($update)){
            $arr = $session->get("like_timer");
            array_push($arr, $id);
            $session->set("like_timer", $arr);

            $repo = $this->getEntityManager()->getRepository(UploadedVideo::class)->findOneBy(array("id" => $id));
            $views = $repo->getViews();
            $repo->setViews($views + 1);
            $em = $this->getEntityManager();
            $em->persist($repo);
            $em->flush();
        }
    }

    public function getCount(){
        $query = $this->createQueryBuilder('uv')
            ->select('count(uv.id)')
            ->getQuery();
        $result = $query->getSingleScalarResult();
        return $result;
    }
}
