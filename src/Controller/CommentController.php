<?php

namespace App\Controller;

use App\Entity\Comment;
use App\Form\CommentType;
use App\Service\TemplateService;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\HttpFoundation\File\Exception\AccessDeniedException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;

class CommentController extends Controller
{

    private $template;

    public function __construct()
    {
        $template = new TemplateService();
        $this->template = $template->getTemplate();
    }

    public function indexAction()
    {
        return $this->render('/front/comment/index.html.twig');
    }

    public function commentAction($identifier){

        $comments = $this->getDoctrine()->getRepository(Comment::class)->findBy(["identifier" => $identifier], ['id' => 'DESC']);

        $form = $this->createFormBuilder()
            ->add('content', TextareaType::class)
            ->add('identifier', HiddenType::class, ['data' => $identifier])
            ->add('submit', SubmitType::class)
            ->setAction($this->generateUrl('comment_post'))
            ->getForm()
        ;

        return $this->render($this->template.'/comment/comment.html.twig', array(
            'form' => $form->createView(),
            'comments' => $comments
        ));
    }

    /**
     * @Route("/post", name="comment_post")
     */
    public function postAction(Request $request){

        $session = new Session();

        $comment = new Comment();
        $form = $this->createFormBuilder($comment)
            ->add('content', TextareaType::class)
            ->add('identifier', HiddenType::class)
            ->add('submit', SubmitType::class)
            ->setAction($this->generateUrl('comment_post'))
            ->getForm()
            ->handleRequest($request)
        ;

        if(!$session->has("lastComment")){
            $session->set("lastComment", date("h:i"));
        }

        if ($form->isSubmitted() && $form->isValid()) {

            if(date("h:i") > $session->get("lastComment")){

                $data = $form->getData();

                $comment->setDate(new \DateTime("now"));
                $comment->setIdentifier($data->getIdentifier());
                $comment->setUser($this->getUser());
                $comment->setUserLikes(array());

                $em = $this->getDoctrine()->getManager();
                $em->persist($comment);
                $em->flush();
                $session->set("lastComment", date("h:i"));
                return new JsonResponse(["status" => "ok", "msg" => "Commentaire ajouté !"]);

            }
            else{
                return new JsonResponse(["status" => "wait", "msg" => "Veuillez patienter une minute avant de poster à nouveau"]);
            }

        }
        return new AccessDeniedException("Not authorized");
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

        return new JsonResponse(["status" => "ok"]);
    }
}
