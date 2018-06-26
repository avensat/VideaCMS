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
        );
    }

    public function getProfilePic(User $user){
        if($user->getProfilePicturePath())
            return 'uploads/user/profilepics/'.$user->getProfilePicturePath();
        return 'uploads/user/profilepics/user_default.png';
    }
}