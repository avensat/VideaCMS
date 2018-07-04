<?php

namespace App\Controller;

use App\Entity\Article;
use App\Entity\Category;
use App\Form\ArticleType;
use App\Form\CategoryType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;

/**
 * @Route("/article")
 */
class NewsController extends Controller
{
    /**
     * @Route("/news", name="news_homepage")
     */
    public function indexAction()
    {
        $articles = $this->getDoctrine()->getRepository(Article::class)->findAll();
        return $this->render('front/news/index.html.twig', array(
            "articles" => $articles
        ));
    }

    /**
     * @Route("/add", name="news_add")
     */
    public function addAction(Request $request){
        $this->denyAccessUnlessGranted('ROLE_EDITOR');

        $article = new Article();

        $form = $this->createForm(ArticleType::class, $article);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $article->setUser($this->getUser());
            $article->setCreation(new \DateTime("now"));
            $em = $this->getDoctrine()->getManager();
            $em->persist($article);
            $em->flush();
            $this->addFlash('success', 'Article publié !');
            return $this->redirectToRoute("news_manage");
        }
        return $this->render('front/news/editor/add.html.twig', array(
            'form' => $form->createView()
        ));
    }

    /**
     * @Route("/add/category", name="news_add_cat")
     */
    public function addCatAction(Request $request){
        $this->denyAccessUnlessGranted('ROLE_EDITOR');

        $category = new Category();
        $form = $this->createForm(CategoryType::class, $category);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($category);
            $em->flush();
            $this->addFlash('success', 'Catégorie ajoutée !');
            return $this->redirectToRoute("news_manage");
        }

        return $this->render('front/news/editor/addCat.html.twig', array(
            'form' => $form->createView()
        ));
    }

    /**
     * @Route("/edit/{id}", name="news_edit", requirements={"id"="\d+"})
     */
    public function editAction(Request $request, $id){
        $this->denyAccessUnlessGranted('ROLE_EDITOR');

        $user = $this->getUser();

        $article = $this->getDoctrine()->getRepository(Article::class)->findOneBy(array("id" => $id, "user" => $user));

        $form = $this->createForm(ArticleType::class, $article);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->flush();
            $this->addFlash('success', 'Article modifié !');
            return $this->redirectToRoute("news_manage");
        }

        return $this->render('front/news/editor/edit.html.twig', array(
            'form' => $form->createView()
        ));

    }

    /**
     * @Route("/edit/category/{id}", name="news_edit_cat", requirements={"id"="\d+"})
     */
    public function editCatAction(Request $request, $id){
        $this->denyAccessUnlessGranted('ROLE_EDITOR');

        $category = $this->getDoctrine()->getRepository(Category::class)->findOneBy(array("id" => $id));

        $form = $this->createForm(CategoryType::class, $category);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->flush();
            $this->addFlash('success', 'Catégorie modifiée !');
            return $this->redirectToRoute("news_manage");
        }

        return $this->render('front/news/editor/editCat.html.twig', array(
            'form' => $form->createView()
        ));

    }

    /**
     * @Route("/delete/{id}", name="news_delete", requirements={"id"="\d+"})
     */
    public function deleteAction(Request $request, $id){
        $this->denyAccessUnlessGranted('ROLE_EDITOR');

        $user = $this->getUser();

        $article = $this->getDoctrine()->getRepository(Article::class)->findOneBy(array("id" => $id, "user" => $user));

        $em = $this->getDoctrine()->getManager();
        $em->remove($article);
        $em->flush();
        $this->addFlash('success', 'Article supprimé !');
        return $this->redirectToRoute("news_manage");

    }

    /**
     * @Route("/delete/category/{id}", name="news_delete_cat", requirements={"id"="\d+"})
     */
    public function deleteCatAction(Request $request, $id){
        $this->denyAccessUnlessGranted('ROLE_EDITOR');

        $category = $this->getDoctrine()->getRepository(Category::class)->findOneBy(array("id" => $id));

        $em = $this->getDoctrine()->getManager();
        $em->remove($category);
        $em->flush();
        $this->addFlash('success', 'Categorie supprimé !');
        return $this->redirectToRoute("news_manage");

    }

    /**
     * @Route("/view/{id}", name="news_view", requirements={"id"="\d+"})
     */
    public function viewAction($id){

        $article = $this->getDoctrine()->getRepository(Article::class)->findOneBy(array("id" => $id));

        return $this->render('front/news/view.html.twig', array(
            'article' => $article
        ));
    }

    /**
     * @Route("/manage", name="news_manage")
     */
    public function manageAction(Request $request){
        $this->denyAccessUnlessGranted('ROLE_EDITOR');

        $user = $this->getUser();

        $em    = $this->get('doctrine.orm.entity_manager');
        $paginator  = $this->get('knp_paginator');

        $dql   = "SELECT a FROM App\Entity\Article a WHERE a.user = ".$user->getId()." ORDER BY a.id DESC";
        $query = $em->createQuery($dql);

        $articles = $paginator->paginate(
            $query, /* query NOT result */
            $request->query->getInt('page', 1)/*page number*/,
            10/*limit per page*/
        );

        $dql   = "SELECT a FROM App\Entity\Category a ORDER BY a.id DESC";
        $query = $em->createQuery($dql);

        $categories = $paginator->paginate(
            $query, /* query NOT result */
            $request->query->getInt('page', 1)/*page number*/,
            10/*limit per page*/
        );

        return $this->render('front/news/editor/manage.html.twig', array(
            'articles' => $articles,
            'categories' => $categories
        ));
    }

}
