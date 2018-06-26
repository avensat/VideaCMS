<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;

class UserController extends Controller
{
    public function indexAction()
    {
        return $this->render('/front/user/index.html.twig');
    }

    /**
     * @Route("/profile/edit/custom", name="user_custom")
     */
    public function editCustomAction(Request $request){

        $userInfo = $this->getDoctrine()->getRepository(User::class)->findOneBy(array("id" => $this->getUser()->getId()));
        $form = $this->get('form.factory')->create(UserType::class, $userInfo);


        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $userInfo->preUploadProfilePicture();
            $userInfo->uploadProfilePicture();

            $em = $this->getDoctrine()->getManager();
            $em->flush();
            $request->getSession()->getFlashBag()->add('success', 'Modifications ok');

            return $this->redirectToRoute('fos_user_profile_edit');
        }

        return $this->render('/front/user/edit_custom.html.twig', array(
            "form" => $form->createView()
        ));
    }

    /**
     * @Route("/user/{username}", name="user_profile", requirements={"username"="[a-z0-9]{2,15}"})
     */
    public function viewProfileAction(Request $request, $username){

        $user = $this->getDoctrine()->getRepository(User::class)->findOneBy(array("username" => $username));


        return $this->render('/front/user/profile/view.html.twig', array(
            "user" => $user
        ));
    }
}
