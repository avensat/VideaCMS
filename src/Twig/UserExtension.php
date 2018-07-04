<?php

namespace App\Twig;

use App\Entity\User;
use Doctrine\ORM\EntityManager;

class UserExtension extends \Twig_Extension
{
    /**
     * @var EntityManager
     */
    protected $em;

    /**
     * GetProvinceExtension constructor.
     * @param EntityManager $em
     */
    public function __construct(EntityManager $em) {
        $this->em = $em;
    }


    public function getFunctions()
    {
        return array(
            new \Twig_SimpleFunction('getProfilePic', array($this, 'getProfilePic')),
            new \Twig_SimpleFunction('getLastUsers', array($this, 'getLastUsers')),
        );
    }

    public function getProfilePic(User $user){
        if($user->getProfilePicturePath())
            return '/uploads/user/profilepics/'.$user->getProfilePicturePath();
        return '/uploads/user/profilepics/user_default.png';
    }

    public function getLastUsers(){
        return $this->em->getRepository(User::class)->findBy([], ['id' => 'DESC']);
    }
}