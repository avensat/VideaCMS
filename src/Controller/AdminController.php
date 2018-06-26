<?php

namespace App\Controller;

use EasyCorp\Bundle\EasyAdminBundle\Controller\AdminController as BaseAdminController;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/admin")
 */
class AdminController extends BaseAdminController
{
    public function createNewUserEntity()
    {
        return $this->get('fos_user.user_manager')->createUser();
    }

    public function persistUserEntity($user)
    {
        $this->get('fos_user.user_manager')->updateUser($user, false);
        parent::persistEntity($user);
    }

    public function updateUserEntity($user)
    {
        $this->get('fos_user.user_manager')->updateUser($user, false);
        parent::updateEntity($user);
    }

    /**
     * @Route("/creator", name="administrationCreator")
     */
    public function administrationCreatorAction()
    {
        return $this->render('/front/administration/index.html.twig');
    }
}
