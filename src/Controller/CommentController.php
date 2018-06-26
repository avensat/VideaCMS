<?php

namespace App\Controller;

use App\Entity\Comment;
use App\Form\CommentType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;

class CommentController extends Controller
{

    public function indexAction()
    {
        return $this->render('/front/comment/index.html.twig');
    }

    public function commentAction(Request $request, $identifier){

        $session = new Session();
        $comments = $this->getDoctrine()->getRepository(Comment::class)->findBy(array("identifier" => $identifier));

        $comment = new Comment();
        $form = $this->createForm(CommentType::class, $comment);
        $form->handleRequest($request);

        if(!$session->has("lastComment")){
            $session->set("lastComment", date("h:i"));
        }

        if ($form->isSubmitted() && $form->isValid()) {

            if(date("h:i") > $session->get("lastComment")){

                $comment->setDate(new \DateTime("now"));
                $comment->setIdentifier($identifier);
                $comment->setUser($this->getUser());
                $comment->setUserLikes(array());

                $em = $this->getDoctrine()->getManager();
                $em->persist($comment);
                $em->flush();
                $request->getSession()->getFlashBag()->add('success', 'Commentaire ajouté !');
                $session->set("lastComment", date("h:i"));
            }
            else{
                $request->getSession()->getFlashBag()->add('success', 'Veuillez patienter une minute');
            }

        }

        return $this->render('/front/comment/comment.html.twig', array(
            'form' => $form->createView(),
            'comments' => $comments
        ));
    }

	/**
     * @Route("/comment/like/{id}", name="comment_like", requirements={"username"="\d+"})
     */
    public function likeAction(Request $request, $id){
        $this->denyAccessUnlessGranted('ROLE_USER');

        $comment = $this->getDoctrine()->getRepository(Comment::class)->findOneBy(array("id" => $id));
        $userLikes = $comment->getUserLikes();
        $likes = $comment->getLikes();
        foreach ($userLikes as $userLike){
            if ($userLike == $this->getUser()->getId())
                $liked = true;
        }

        if(isset($liked)){
            $likes -= 1;
            $userId = array_search($this->getUser()->getId(), $userLikes);
            unset($userLikes[$userId]);
        }
        else{
            $likes += 1;
            array_push($userLikes, $this->getUser()->getId());
        }

        $comment->setLikes($likes);
        $comment->setUserLikes($userLikes);

        $em = $this->getDoctrine()->getManager();
        $em->persist($comment);
        $em->flush();

        return $this->redirectToRoute($request->getUri());
    }
}
