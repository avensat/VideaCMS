<?php

namespace App\Controller;

use App\Entity\Message;
use App\Entity\Thread;
use App\Form\MessageType;
use App\Form\ThreadType;
use App\Repository\ThreadRepository;
use App\Service\TemplateService;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * @Route("/forum")
 */
class ThreadController extends Controller
{

    private $template;

    public function __construct()
    {
        $template = new TemplateService();
        $this->template = $template->getTemplate();
    }

    /**
     * @Route("/", name="thread_index", methods="GET|POST")
     */
    public function index(Request $request): Response
    {
        $thread = new Thread();
        $messages = $this->getDoctrine()->getRepository(Message::class)->findAll();
        $form = $this->createForm(ThreadType::class, $thread);
        $form->handleRequest($request);

        $em    = $this->get('doctrine.orm.entity_manager');
        $dql   = 'SELECT a FROM App\Entity\Thread a ORDER BY a.last_message DESC';
        $query = $em->createQuery($dql);

        $paginator  = $this->get('knp_paginator');
        $threads = $paginator->paginate(
            $query, /* query NOT result */
            $request->query->getInt('page', 1)/*page number*/,
            20/*limit per page*/
        );

        if ($form->isSubmitted() && $form->isValid()) {
            $this->denyAccessUnlessGranted('ROLE_USER');
            $data = $form->getData();
            if(strlen($data->getContent()) <= 9 || strlen($data->getContent() >= 5000)){
                $this->addFlash("error", "Le texte ne doit pas faire moins de 3 caractères ou plus de 5 000 caractères.");
                return $this->redirectToRoute("thread_index", ["id" => $thread->getId()]);
            }
            $thread->setUser($this->getUser());
            $thread->setStatus("open");
            $em = $this->getDoctrine()->getManager();
            $em->persist($thread);
            $em->flush();
            $this->addFlash('success', "Sujet ajouté");
            return $this->redirectToRoute('thread_index');
        }

        return $this->render($this->template.'/thread/index.html.twig', [
            'threads' => $threads,
            'messages' => $messages,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/new", name="thread_new", methods="GET|POST")
     */
    public function new(Request $request): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');
        $thread = new Thread();
        $form = $this->createForm(ThreadType::class, $thread);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $thread->setUser($this->getUser());
            $thread->setStatus("open");
            $em = $this->getDoctrine()->getManager();
            $em->persist($thread);
            $em->flush();
            $this->addFlash('success', "Sujet ajouté");
            return $this->redirectToRoute('thread_index');
        }

        return $this->render($this->template.'/thread/new.html.twig', [
            'thread' => $thread,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/change_status/{id}/{action}", name="thread_change_status", methods="GET")
     */
    public function changeStatus(Thread $thread, $action): Response
    {
        $this->denyAccessUnlessGranted('ROLE_MODERATOR');

        $status = $thread->getStatus();
        $locked = $thread->getLocked();
        if($action == "lock"){
            if($locked){
                $thread->setLocked(false);
                $this->addFlash("success", "Sujet dévérouillé");
            }
            elseif (!$locked){
                $thread->setLocked(true);
                $this->addFlash("success", "Sujet verouillé");
            }

        }
        elseif ($action == "pin"){
            if($status == "pinned"){
                $thread->setStatus("open");
                $this->addFlash("success", "Sujet desépinglé");
            }
            elseif ($status == "open"){
                $thread->setStatus("pinned");
                $this->addFlash("success", "Sujet épinglé");
            }

        }
        $this->getDoctrine()->getManager()->flush();

        return $this->redirectToRoute("thread_show", ["id" => $thread->getId()]);
    }

    /**
     * @Route("/{id}", name="thread_show", methods="GET|POST")
     */
    public function show(Thread $thread, Request $request): Response
    {
        $message = new Message();
        $form = $this->createForm(MessageType::class, $message)->handleRequest($request);

        $em    = $this->get('doctrine.orm.entity_manager');
        $dql   = 'SELECT a FROM App\Entity\Message a WHERE a.thread = '.$thread->getId().' ORDER BY a.id ASC';
        $query = $em->createQuery($dql);

        $paginator  = $this->get('knp_paginator');
        $messages = $paginator->paginate(
            $query, /* query NOT result */
            $request->query->getInt('page', 1)/*page number*/,
            10
        );

        if($form->isValid() && $form->isSubmitted()){
            $this->denyAccessUnlessGranted('ROLE_USER');
            if($thread->getLocked())
                throw new AccessDeniedException();

            $data = $form->getData();
            if(strlen($data->getContent()) <= 9 || strlen($data->getContent() >= 5000)){
                $this->addFlash("error", "Le texte ne doit pas faire moins de 3 caractères ou plus de 5 000 caractères.");
                return $this->redirectToRoute("thread_show", ["id" => $thread->getId()]);
            }
            $thread->setLastMessage(new \DateTime("now"));
            $message->setUser($this->getUser());
            $message->setThread($thread);
            $em = $this->getDoctrine()->getManager();
            $em->persist($message);
            $em->flush();
            $this->addFlash("success", "Sujet posté");
            return $this->redirectToRoute("thread_show", ["id" => $thread->getId()]);
        }
        return $this->render($this->template.'/thread/show.html.twig', [
            'thread' => $thread,
            'messages' => $messages,
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/{id}/edit", name="thread_edit", methods="GET|POST")
     */
    public function edit(Request $request, Thread $thread): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');
        if($thread->getUser() != $this->getUser() && !$this->get('security.context_listener.0')->isGranted('ROLE_MODERATOR'))
            throw new AccessDeniedException();

        $form = $this->createForm(ThreadType::class, $thread);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $thread->setLastModification(new \DateTime("now"));
            $em = $this->getDoctrine()->getManager();
            $em->persist($thread);
            $em->flush();

            $this->addFlash("success", "Sujet edité");
            return $this->redirectToRoute('thread_show', ['id' => $thread->getId()]);
        }

        return $this->render($this->template.'/thread/edit.html.twig', [
            'thread' => $thread,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="thread_delete", methods="DELETE")
     */
    public function delete(Request $request, Thread $thread): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');
        if ($this->isCsrfTokenValid('delete'.$thread->getId(), $request->request->get('_token'))) {
            $em = $this->getDoctrine()->getManager();
            $em->remove($thread);
            $em->flush();
        }

        return $this->redirectToRoute('thread_index');
    }
}
