<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;

class UserController extends Controller
{

    private $tokenManager;

    public function __construct(CsrfTokenManagerInterface $tokenManager = null)
    {
        $this->tokenManager = $tokenManager;
    }

    public function indexAction()
    {
        return $this->render('/front/user/index.html.twig');
    }

    /**
     * @Route("/profile/edit/custom", name="user_custom")
     */
    public function editCustomAction(Request $request){

        $userInfo = $this->getUser();

        $form = $this->createFormBuilder()
            ->add('twitter', UrlType::class, ['data' => $userInfo->getTwitter(), 'required' => false])
            ->add('facebook', UrlType::class, ['data' => $userInfo->getFacebook(), 'required' => false])
            ->add('youtube', UrlType::class, ['data' => $userInfo->getYoutube(), 'required' => false])
            ->add('biography', TextareaType::class, ['data' => $userInfo->getBiography(), 'required' => false])
            ->add('submit', SubmitType::class)
            ->getForm()->handleRequest($request);

        $formPicture = $this->createFormBuilder()
            ->add('profilePicture', FileType::class)
            ->add('submit', SubmitType::class)
            ->setAction($this->generateUrl("user_custom_picture"))
            ->getForm();

        if ($form->isSubmitted() && $form->isValid()) {

            $data = $form->getData();

            $userInfo->setTwitter($data['twitter']);
            $userInfo->setFacebook($data['facebook']);
            $userInfo->setYoutube($data['youtube']);
            $userInfo->setBiography($data['biography']);

            $em = $this->getDoctrine()->getManager();
            $em->flush();
            $this->addFlash('success', 'Informations sauvegardés.');
            return $this->redirectToRoute('user_custom');
        }

        return $this->render('/front/user/edit_custom.html.twig', array(
            "form" => $form->createView(),
            "formPicture" => $formPicture->createView()
        ));
    }

    /**
     * @Route("/profile/edit/picture", name="user_custom_picture")
     * @Method({"POST"})
     */
    public function editPictureAction(Request $request){
        $formPicture = $this->createFormBuilder()
            ->add('profilePicture', FileType::class)
            ->add('submit', SubmitType::class)
            ->setAction($this->generateUrl("user_custom_picture"))
            ->getForm()->handleRequest($request);

        if ($formPicture->isSubmitted() && $formPicture->isValid()) {

            $data = $formPicture->getData();
            $userInfo = $this->getUser();

            $userInfo->setProfilePictureFile($data['profilePicture']);
            $userInfo->preUploadProfilePicture();
            $userInfo->uploadProfilePicture();

            $em = $this->getDoctrine()->getManager();
            $em->flush();

            $this->addFlash('success', "Photo de profil modifiée !");
        }
        return $this->redirectToRoute("user_custom");
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

    public function getTokenAction()
    {
        return new Response($this->tokenManager->getToken('authenticate')->getValue());
    }
}
