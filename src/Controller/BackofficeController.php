<?php

namespace App\Controller;

use App\Entity\Message;
use App\Entity\Thread;
use App\Entity\User;
use App\Entity\Wall;
use App\Form\UserType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;

/**
 * @Route("/backoffice")
 */

class BackofficeController extends Controller
{
    /**
     * @Route("/", name="backoffice_index")
     */
    public function index()
    {
        $users = $this->getDoctrine()->getRepository(User::class)->findAll();

        return $this->render('backoffice/index.html.twig', [
            'users' => $users,
        ]);
    }

    // USERS CONTROLLERS

    /**
     * @Route("/users", name="backoffice_users")
     */
    public function listUser(Request $request)
    {
        $query = $this->getDoctrine()->getRepository(User::class)->findBy([], ["id" => "DESC"]);

        $paginator  = $this->get('knp_paginator');
        $users = $paginator->paginate($query, $request->query->getInt('page', 1), 20);

        return $this->render('backoffice/Users/index.html.twig', [
            'users' => $users,
        ]);
    }

    /**
     * @Route("/users/{id}", name="backoffice_show_user")
     */
    public function showUser(User $user){
        return $this->render('backoffice/Users/show.html.twig', ['user' => $user]);
    }

    /**
     * @Route("/users/all/{type}/{id}", name="backoffice_user_all")
     */
    public function showAllForUser($type, User $user){
        if($type == "wall"){
            $data = $this->getDoctrine()->getRepository(Wall::class)->findBy(["user" => $user], ["id" => "DESC"]);
            $messages = null;
        }
        elseif($type == "forum"){
            $data = $this->getDoctrine()->getRepository(Thread::class)->findBy(['user' => $user], ['id' => "DESC"]);
            $messages = $this->getDoctrine()->getRepository(Message::class)->findBy(['user' => $user], ['id' => "DESC"]);
        }
        else
            throw new BadRequestHttpException();

        return $this->render('backoffice/Users/showAll.html.twig', [
            'data' => $data,
            'messages' => $messages,
            'type' => $type,
            'user' => $user
        ]);
    }

    /**
     * @Route("/users/block/{id}", name="backoffice_user_block")
     */
    public function blockUser(User $user){
        $status = $this->getDoctrine()->getRepository(User::class)->findOneBy(['id' => $user, 'enabled' => 1]);
        if($status)
            $user->setEnabled(false);
        else
            $user->setEnabled(true);
        $this->getDoctrine()->getManager()->flush();
        $this->addFlash("success", "Status changé.");
        return $this->redirectToRoute("backoffice_user_show", ["id" => $user->getId()]);
    }

    /**
     * @Route("/users/roles/{id}", name="backoffice_user_roles")
     */
    public function userRoles(User $user){

        $admin = $user->hasRole("ROLE_ADMIN");
        $moderator = $user->hasRole("ROLE_MODERATOR");
        $creator = $user->hasRole("ROLE_CREATOR");

        return $this->render('backoffice/Users/roles.html.twig', [
            "user" => $user,
            "admin" => $admin,
            "moderator" => $moderator,
            "creator" => $creator
        ]);
    }

    /**
     * @Route("/users/role/{id}/{type}", name="backoffice_user_roles_manage")
     */
    public function manageRole(User $user, $type){
        if($type == "admin"){
            if($user->hasRole("ROLE_ADMIN"))
                $user->removeRole("ROLE_ADMIN");
            else
                $user->addRole("ROLE_ADMIN");
        }
        elseif($type == "moderator"){
            if($user->hasRole("ROLE_MODERATOR"))
                $user->removeRole("ROLE_MODERATOR");
            else
                $user->addRole("ROLE_MODERATOR");
        }
        elseif($type == "creator"){
            if($user->hasRole("ROLE_CREATOR")){
                $user->removeRole("ROLE_CREATOR");
                $user->setCertified(null);
            }
            else {
                $user->addRole("ROLE_CREATOR");
                $user->setCertified("1");
            }
        }
        else
            throw new BadRequestHttpException();

        $this->getDoctrine()->getManager()->flush();
        return $this->redirectToRoute("backoffice_user_roles", ["id" => $user->getId()]);
    }

    /**
     * @Route("/users/edit/{id}", name="backoffice_user_edit")
     */
    public function editUser(User $user, Request $request){
        $form = $this->createFormBuilder()
            ->add('twitter', UrlType::class, ['data' => $user->getTwitter(), 'required' => false])
            ->add('facebook', UrlType::class, ['data' => $user->getFacebook(), 'required' => false])
            ->add('youtube', UrlType::class, ['data' => $user->getYoutube(), 'required' => false])
            ->add('biography', TextareaType::class, ['data' => $user->getBiography(), 'required' => false])
            ->add('submit', SubmitType::class)
            ->getForm()->handleRequest($request);

        $formPicture = $this->createFormBuilder()
            ->add('profilePicture', FileType::class)
            ->add('submit', SubmitType::class)
            ->setAction($this->generateUrl("backoffice_user_edit_picture", ["id" => $user->getId()]))
            ->getForm();

        $formPseudo = $this->createFormBuilder()
            ->add('username', TextType::class, ["data" => $user->getUsername()])
            ->add('submit', SubmitType::class)
            ->setAction($this->generateUrl("backoffice_user_edit_pseudo", ["id" => $user->getId()]))
            ->getForm();

        $formPassword = $this->createFormBuilder()
            ->add('password1', PasswordType::class)
            ->add('password2', PasswordType::class)
            ->add('submit', SubmitType::class)
            ->setAction($this->generateUrl("backoffice_user_edit_password", ["id" => $user->getId()]))
            ->getForm();

        if ($form->isSubmitted() && $form->isValid()) {

            $data = $form->getData();

            $user->setTwitter($data['twitter']);
            $user->setFacebook($data['facebook']);
            $user->setYoutube($data['youtube']);
            $user->setBiography($data['biography']);

            $em = $this->getDoctrine()->getManager();
            $em->flush();
            $this->addFlash('success', 'Profil modifié.');
            return $this->redirectToRoute('backoffice_user_edit', ["id" => $user->getId()]);
        }

        return $this->render('backoffice/Users/edit.html.twig', [
            "user" => $user,
            "form" => $form->createView(),
            "formPicture" => $formPicture->createView(),
            "formPseudo" => $formPseudo->createView(),
            "formPassword" => $formPassword->createView(),
        ]);
    }

    /**
     * @Route("/users/edit/picture/{id}", name="backoffice_user_edit_picture")
     * @Method({"POST"})
     */
    public function editPictureAction(Request $request, User $user){
        $formPicture = $this->createFormBuilder()
            ->add('profilePicture', FileType::class)
            ->add('submit', SubmitType::class)
            ->setAction($this->generateUrl("user_custom_picture"))
            ->getForm()->handleRequest($request);

        if ($formPicture->isSubmitted() && $formPicture->isValid()) {

            $data = $formPicture->getData();

            $user->setProfilePictureFile($data['profilePicture']);
            $user->preUploadProfilePicture();
            $user->uploadProfilePicture();

            $em = $this->getDoctrine()->getManager();
            $em->flush();
            $this->addFlash('success', "Photo de profil modifiée !");
        }
        return $this->redirectToRoute("backoffice_user_edit", ["id" => $user->getId()]);
    }

    /**
     * @Route("/users/edit/pseudo/{id}", name="backoffice_user_edit_pseudo")
     * @Method({"POST"})
     */
    public function editPseudoAction(Request $request, User $user){
        $formPseudo = $this->createFormBuilder()
            ->add('username', TextType::class)
            ->add('submit', SubmitType::class)
            ->setAction($this->generateUrl("backoffice_user_edit_pseudo", ["id" => $user->getId()]))
            ->getForm()->handleRequest($request);

        if($formPseudo->isSubmitted() && $formPseudo->isValid()){
            $data = $formPseudo->getData();
            $user->setUsername($data["username"]);
            $this->getDoctrine()->getManager()->flush();
            $this->addFlash("success", "Pseudo changé.");
            return $this->redirectToRoute("backoffice_user_edit", ["id" => $user->getId()]);
        }
        throw new BadRequestHttpException();
    }

    /**
     * @Route("/users/edit/password/{id}", name="backoffice_user_edit_password")
     * @Method({"POST"})
     */
    public function editPasswordAction(Request $request, User $user){
        $formPassword = $this->createFormBuilder()
            ->add('password1', PasswordType::class)
            ->add('password2', PasswordType::class)
            ->add('submit', SubmitType::class)
            ->setAction($this->generateUrl("backoffice_user_edit_password", ["id" => $user->getId()]))
            ->getForm()->handleRequest($request);

        if($formPassword->isSubmitted() && $formPassword->isValid()){
            $data = $formPassword->getData();
            if($data["password1"] == $data["password2"]){
                $user->setPlainPassword($data["password1"]);
                $userManager = $this->get('fos_user.user_manager');
                $userManager->updatePassword($user);
                $this->getDoctrine()->getManager()->flush();
                $this->addFlash("success", "Mot de passe changé.");
            }
            else{
                $this->addFlash("error", "Les mots de passe ne correspondent pas.");
            }
            return $this->redirectToRoute("backoffice_user_edit", ["id" => $user->getId()]);
        }
        throw new BadRequestHttpException();
    }
}
