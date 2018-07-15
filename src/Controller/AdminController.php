<?php

namespace App\Controller;

use App\Entity\Article;
use App\Entity\Comment;
use App\Entity\Poll;
use App\Entity\ProviderVideo;
use App\Entity\UploadedVideo;
use App\Entity\User;
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

    /**
     * @Route("/dashboard", name="admin_dashboard")
     */
    public function dashboard()
    {
        $doctrine = $this->getDoctrine();
        return $this->render('admin/dashboard.html.twig', [
            'users_count' => $doctrine->getRepository(User::class)->getCount(),
            'polls_count' => $doctrine->getRepository(Poll::class)->getCount(),
            'videos_count' => $doctrine->getRepository(UploadedVideo::class)->getCount()+
                $doctrine->getRepository(ProviderVideo::class)->getCount(),
            'comments_count' => $doctrine->getRepository(Comment::class)->getCount(),
            'articles_count' => $doctrine->getRepository(Article::class)->getCount(),
        ]);
    }
}
