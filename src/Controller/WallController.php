<?php

namespace App\Controller;

use App\Entity\Wall;
use App\Form\WallType;
use App\Repository\WallRepository;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/wall")
 */
class WallController extends Controller
{
    /**
     * @Route("/", name="wall_index", methods="GET|POST")
     */
    public function index(WallRepository $wallRepository, Request $request): Response
    {
        $wall = new Wall();
        $form = $this->createForm(WallType::class, $wall);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $wall->setUserId($this->getUser());
            $em = $this->getDoctrine()->getManager();
            $em->persist($wall);
            $em->flush();

            return $this->redirectToRoute('wall_index');
        }

        $em    = $this->get('doctrine.orm.entity_manager');
        $dql   = "SELECT a FROM App\Entity\Wall a ORDER BY a.id DESC";
        $query = $em->createQuery($dql);

        $paginator  = $this->get('knp_paginator');
        $walls = $paginator->paginate(
            $query, /* query NOT result */
            $request->query->getInt('page', 1)/*page number*/,
            20/*limit per page*/
        );


        return $this->render('wall/index.html.twig', [
            'walls' => $walls,
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/new", name="wall_new", methods="GET|POST")
     */
    public function new(Request $request): Response
    {
        $wall = new Wall();
        $form = $this->createForm(WallType::class, $wall);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $wall->setUserId($this->getUser());
            $em = $this->getDoctrine()->getManager();
            $em->persist($wall);
            $em->flush();

            return $this->redirectToRoute('wall_index');
        }

        return $this->render('wall/new.html.twig', [
            'wall' => $wall,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="wall_show", methods="GET")
     */
    public function show(Wall $wall): Response
    {
        return $this->render('wall/show.html.twig', ['wall' => $wall]);
    }

    /**
     * @Route("/{id}/edit", name="wall_edit", methods="GET|POST")
     */
    public function edit(Request $request, Wall $wall): Response
    {
        $form = $this->createForm(WallType::class, $wall);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('wall_edit', ['id' => $wall->getId()]);
        }

        return $this->render('wall/edit.html.twig', [
            'wall' => $wall,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="wall_delete", methods="DELETE")
     */
    public function delete(Request $request, Wall $wall): Response
    {
        if ($this->isCsrfTokenValid('delete'.$wall->getId(), $request->request->get('_token'))) {
            $em = $this->getDoctrine()->getManager();
            $em->remove($wall);
            $em->flush();
        }

        return $this->redirectToRoute('wall_index');
    }
}
