<?php

namespace App\Controller;

use App\Entity\Message;
use App\Entity\Thread;
use App\Entity\User;
use App\Entity\Wall;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

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
}
