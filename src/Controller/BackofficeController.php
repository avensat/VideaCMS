<?php

namespace App\Controller;

use App\Entity\Article;
use App\Entity\Comment;
use App\Entity\ForumRank;
use App\Entity\Message;
use App\Entity\Poll;
use App\Entity\ProviderVideo;
use App\Entity\Report;
use App\Entity\Theme;
use App\Entity\Thread;
use App\Entity\UploadedVideo;
use App\Entity\User;
use App\Entity\Wall;
use App\Form\ArticleType;
use App\Form\CommentType;
use App\Form\ForumRankType;
use App\Form\ThemeParametersType;
use App\Form\ThemeType;
use App\Form\UploadType;
use App\Form\UserType;
use FFMpeg\Coordinate\Dimension;
use FFMpeg\Coordinate\TimeCode;
use FFMpeg\FFMpeg;
use FOS\UserBundle\Form\Type\RegistrationFormType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\Yaml\Yaml;

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
        return $this->redirectToRoute("backoffice_show_user", ["id" => $user->getId()]);
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

    /**
     * @Route("/user/add", name="backoffice_user_add")
     */
    public function addUser(Request $request){

        $user = new User();
        $form = $this->createForm(RegistrationFormType::class, $user)->handleRequest($request);

        if($form->isValid() && $form->isSubmitted()) {
            $userManager = $this->get('fos_user.user_manager');
            $exists = $userManager->findUserBy(array('email' => $user->getEmail()));
            if ($exists instanceof User) {
                $this->addFlash('errror', 'Cet email existe déjà');
            }
            $userManager->updateUser($user);
            $this->addFlash('success', "L'utilisateur a bien été créé.");
            return $this->redirectToRoute("backoffice_users");
        }

        return $this->render('backoffice/Users/add.html.twig', [
            "form" => $form->createView()
        ]);
    }

    /**
     * @Route("/videos/hosted", name="backoffice_videos_hosted")
     */
    public function hostedVideos(Request $request){
        $query = $this->getDoctrine()->getRepository(UploadedVideo::class)->findBy([], ["id" => "DESC"]);
        $paginator  = $this->get('knp_paginator');
        $videos = $paginator->paginate($query, $request->query->getInt('page', 1), 20);

        return $this->render('backoffice/Videos/hosted.html.twig', [
            "videos" => $videos
        ]);
    }

    /**
     * @Route("/videos/hosted/edit/{id}", name="backoffice_videos_hosted_edit")
     */
    public function editHostedVideos(UploadedVideo $video, Request $request){

        $form = $this->createFormBuilder()
            ->add("title", TextType::class, ["data" => $video->getTitle()])
            ->add("description", TextareaType::class, ["data" => $video->getDescription()])
            ->add("public", CheckboxType::class, ["data" => $video->getPublic()])
            ->getForm()
            ->handleRequest($request)
        ;

        $formVideo = $this->createFormBuilder()
            ->add("video", FileType::class)
            ->setAction($this->generateUrl("backoffice_video_edit_upload", ['id' => $video->getId()]))
            ->getForm();

        if($form->isSubmitted() && $form->isValid()){
            $data = $form->getData();
            $video->setTitle($data['title']);
            $video->setDescription($data['description']);
            $video->setPublic($data['public']);
            $this->getDoctrine()->getManager()->flush();
            $this->addFlash("success", "Informations modifiés");
            return $this->redirectToRoute("backoffice_videos_hosted_edit", ["id" => $video->getId()]);
        }

        return $this->render('backoffice/Videos/editHosted.html.twig', [
            "video" => $video,
            "form" => $form->createView(),
            "formVideo" => $formVideo->createView()
        ]);
    }

    /**
     * @Route("/videos/edit/upload/{id}", name="backoffice_video_edit_upload")
     * @Method({"POST"})
     */
    public function editHostedVideo(Request $request, UploadedVideo $upVideo){
        $formVideo = $this->createFormBuilder()
            ->add("video", FileType::class)
            ->setAction($this->generateUrl("backoffice_video_edit_upload", ['id' => $upVideo->getId()]))
            ->getForm()
            ->handleRequest($request)
        ;

        if($formVideo->isSubmitted() && $formVideo->isValid()){
            $upVideo->preUploadVideo();
            $upVideo->uploadVideo();

            // Generate thumbnail

            $ffmpeg = FFMpeg::create(array(
                'ffmpeg.binaries'  => $_ENV["FFMPEG_PATH"],
                'ffprobe.binaries' => $_ENV["FFPROBE_PATH"],
                'timeout'          => 3600,
                'ffmpeg.threads'   => 12,
            ));

            $publicPath = $this->get('kernel')->getRootDir() . '/../public/';

            $video = $ffmpeg->open($publicPath.'uploads/videos/'.$upVideo->getVideoPath());

            $video
                ->filters()
                ->resize(new Dimension(320, 240))
                ->synchronize();

            $random = random_bytes(32);
            $randomString = bin2hex($random);

            $video
                ->frame(TimeCode::fromSeconds(2))
                ->save($publicPath.'uploads/videos/thumbnails/'.$randomString.'.jpg');

            $upVideo->setThumbnail($randomString.'.jpg');
            $em = $this->getDoctrine()->getManager();
            $em->persist($upVideo);
            $em->flush();
            $this->addFlash("success", "Vidéo modifiée");
            return $this->redirectToRoute("backoffice_videos_hosted_edit", ["id" => $upVideo->getId()]);
        }
        throw new BadRequestHttpException();
    }

    /**
     * @Route("/videos/links", name="backoffice_videos_linked")
     */
    public function linkedVideos(Request $request){
        $query = $this->getDoctrine()->getRepository(ProviderVideo::class)->findBy([], ["id" => "DESC"]);
        $paginator  = $this->get('knp_paginator');
        $videos = $paginator->paginate($query, $request->query->getInt('page', 1), 20);

        return $this->render('backoffice/Videos/links.html.twig', [
            "videos" => $videos
        ]);
    }

    /**
     * @Route("/forum/threads", name="backoffice_forum_threads")
     */
    public function threadList(Request $request){

        $query = $this->getDoctrine()->getRepository(Thread::class)->findBy([], ["id" => "DESC"]);
        $paginator  = $this->get('knp_paginator');
        $threads = $paginator->paginate($query, $request->query->getInt('page', 1), 20);

        return $this->render('backoffice/Forum/threads.html.twig', [
            "threads" => $threads
        ]);
    }

    /**
     * @Route("/forum/messages", name="backoffice_forum_messages")
     */
    public function messagesList(Request $request){

        $query = $this->getDoctrine()->getRepository(Message::class)->findBy([], ["id" => "DESC"]);
        $paginator  = $this->get('knp_paginator');
        $messages = $paginator->paginate($query, $request->query->getInt('page', 1), 20);

        return $this->render('backoffice/Forum/messages.html.twig', [
            "messages" => $messages
        ]);
    }

    /**
     * @Route("/articles/", name="backoffice_articles")
     */
    public function articlesList(Request $request){
        $query = $this->getDoctrine()->getRepository(Article::class)->findBy([], ["id" => "DESC"]);
        $paginator  = $this->get('knp_paginator');
        $articles = $paginator->paginate($query, $request->query->getInt('page', 1), 20);

        return $this->render('backoffice/Articles/index.html.twig', [
            "articles" => $articles
        ]);
    }

    /**
     * @Route("/articles/edit/{id}", name="backoffice_articles_edit")
     */
    public function articlesEdit(Request $request, Article $article){

        $form = $this->createForm(ArticleType::class, $article)->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()){
            $this->getDoctrine()->getManager()->flush();
            $this->addFlash("success", "Article modifié");
            return $this->redirectToRoute("backoffice_articles_edit", ["id" => $article->getId()]);
        }

        return $this->render('backoffice/Articles/edit.html.twig', [
            "article" => $article,
            "form" => $form->createView()
        ]);
    }

    /**
     * @Route("/articles/remove/{id}", name="backoffice_articles_remove")
     */
    public function articlesRemove(Request $request, Article $article){
        $em = $this->getDoctrine()->getManager();
        $em->remove($article);
        $em->flush();
        $this->addFlash("success", "Article supprimé");
        return $this->redirectToRoute("backoffice_articles");
    }


    /**
     * @Route("/polls/", name="backoffice_polls")
     */
    public function polls(Request $request){

        $query = $this->getDoctrine()->getRepository(Poll::class)->findBy([], ["id" => "DESC"]);
        $paginator  = $this->get('knp_paginator');
        $polls = $paginator->paginate($query, $request->query->getInt('page', 1), 20);

        return $this->render('backoffice/Polls/index.html.twig', [
            "polls" => $polls
        ]);
    }

    /**
     * @Route("/polls/edit/{id}", name="backoffice_poll_edit")
     */
    public function editPoll(Request $request, Poll $poll){
        $choices = $poll->getChoice();

        $form = $this->createFormBuilder()
            ->add('title', TextType::class, ['data' => $poll->getTitle()])
            ->add('question', TextType::class, ['data' => $poll->getQuestion()])
            ->add('poll', HiddenType::class)
            ->getForm()
            ->handleRequest($request)
        ;

        if($form->isSubmitted()){
            $data = $form->getData();

            $poll->setTitle($data['title']);
            $poll->setQuestion($data['question']);
            $out = [];
            foreach ($data['poll'] as $key => $value){
                $in = ["id" => $key, "value" => $value];
                array_push($out, $in);
            }
            $poll->setChoice($out);

            $em = $this->getDoctrine()->getManager();
            $em->persist($poll);
            $em->flush();
            $this->addFlash('success', 'Sondage modifié !');
            return $this->redirectToRoute("backoffice_poll_edit", ['id' => $poll->getId()]);
        }

        return $this->render('backoffice/Polls/edit.html.twig', [
            "poll" => $poll,
            'form' => $form->createView(),
            'choices' => $choices
        ]);
    }

    /**
     * @Route("/polls/delete/{id}", name="backoffice_poll_delete")
     */
    public function deletePoll(Request $request, Poll $poll){
        $em = $this->getDoctrine()->getManager();
        $em->remove($poll);
        $em->flush();
        $this->addFlash("success", "Sondage supprimé");
        return $this->redirectToRoute("backoffice_polls");
    }

    /**
     * @Route("/appearance", name="backoffice_appearance")
     */
    public function appearance(Request $request){

        $config = Yaml::parseFile('../config/videa.yaml');

        $themeForm = $this->createFormBuilder()
            ->add('themes', EntityType::class, [
                "class" => Theme::class,
                'choice_label' => 'name',
                'placeholder' => 'Choisissez un thème',
            ])
            ->setAction($this->generateUrl("backoffice_change_theme"))
            ->getForm();

        $currentThemeObject = $this->getDoctrine()->getRepository(Theme::class)->findOneBy(["name" => $config['global']['theme']]);
        $parametersForm = $this->createForm(ThemeType::class, $currentThemeObject)->handleRequest($request);

        if($parametersForm->isValid() && $parametersForm->isSubmitted()){
            $this->getDoctrine()->getManager()->flush();
            $this->addFlash("success", "Paramètres du thème modifié");
            return $this->redirectToRoute("backoffice_appearance");
        }

        return $this->render('backoffice/Appearance/index.html.twig', [
            'theme' => $currentThemeObject,
            'themeForm' => $themeForm->createView(),
            'parametersForm' => $parametersForm->createView(),
        ]);
    }

    /**
     * @Route("/appearance/change-theme", name="backoffice_change_theme", methods="POST")
     */
    public function appearanceAdd(Request $request){
        $form = $this->createFormBuilder()
            ->add('themes', EntityType::class, [
                "class" => Theme::class,
                'choice_label' => 'name',
            ])
            ->getForm()
            ->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()){
            $data = $form->getData();
            $config = Yaml::parseFile('../config/videa.yaml');
            $config['global']['theme'] = $data['themes']->getFolder();
            $yaml = Yaml::dump($config);
            file_put_contents('../config/videa.yaml', $yaml);
            $this->addFlash("success", "Thème changé !");
            return $this->redirectToRoute("backoffice_appearance");
        }
        return null;
    }

    /**
     * @Route("/appearance/add", name="backoffice_appearance_add")
     */
    public function changeTheme(Request $request){
        $theme = new Theme();
        $theme->setParameters([
            ["parameter" => "test", "value" => "test2"],
            ["parameter" => "testtest", "value" => "test3"]
        ]);
        $form = $this->createForm(ThemeType::class, $theme)->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()){
            $em = $this->getDoctrine()->getManager();
            $em->persist($theme);
            $em->flush();
        }
        return $this->render('backoffice/Appearance/add.html.twig', [
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/appearance/edit/{id}", name="backoffice_appearance_edit")
     */
    public function appearanceEdit(Request $request, Theme $theme){
        $form = $this->createForm(ThemeType::class, $theme)->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()){
            $this->getDoctrine()->getManager()->flush();
            $this->addFlash("success", "Le thème a bien été modifié");
            return $this->redirectToRoute("backoffice_appearance_edit", ["id" => $theme->getId()]);
        }
        return $this->render('backoffice/Appearance/edit.html.twig', [
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/parameters", name="backoffice_parameters")
     */
    public function parameters(Request $request){
        $config = Yaml::parseFile('../config/videa.yaml');

        $form = $this->createFormBuilder()
            ->add("name", TextType::class, ["data" => $config['global']['site_name']])
            ->add('description', TextType::class, ["data" => $config['global']['description']])
            ->add('facebook', UrlType::class, ["data" => $config['global']['social']['facebook'], "required" => false])
            ->add('twitter', UrlType::class, ["data" => $config['global']['social']['twitter'], "required" => false])
            ->add('youtube', UrlType::class, ["data" => $config['global']['social']['youtube'], "required" => false])
            ->getForm()
            ->handleRequest($request);

        if($form->isValid() && $form->isSubmitted()){
            $data = $form->getData();
            $config['global']['site_name'] = $data['name'];
            $config['global']['description'] = $data['description'];
            $config['global']['social']['facebook'] = $data['facebook'];
            $config['global']['social']['twitter'] = $data['twitter'];
            $config['global']['social']['youtube'] = $data['youtube'];
            $yaml = Yaml::dump($config);
            file_put_contents('../config/videa.yaml', $yaml);
            $this->addFlash("success", "Paramètres enregistrés !");
            return $this->redirectToRoute("backoffice_parameters");
        }
        return $this->render('backoffice/Parameters/index.html.twig', [
            "form" => $form->createView()
        ]);
    }

    /**
     * @Route("/comments/", name="backoffice_comments")
     */
    public function commentsList(Request $request){
        $query = $this->getDoctrine()->getRepository(Comment::class)->findBy([], ["id" => "DESC"]);
        $paginator  = $this->get('knp_paginator');
        $comments = $paginator->paginate($query, $request->query->getInt('page', 1), 20);

        return $this->render('backoffice/Comments/index.html.twig', [
            "comments" => $comments
        ]);
    }


    /**
     * @Route("/comment/edit/{id}", name="backoffice_comment_edit")
     */
    public function commentEdit(Request $request, Comment $comment){

        $form = $this->createForm(CommentType::class, $comment)->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()){
            $this->getDoctrine()->getManager()->flush();
            $this->addFlash("success", "Commentaire modifié");
            return $this->redirectToRoute("backoffice_comment_edit", ["id" => $comment->getId()]);
        }

        return $this->render('backoffice/Comments/edit.html.twig', [
            "comment" => $comment,
            "form" => $form->createView()
        ]);
    }

    /**
     * @Route("/comment/remove/{id}", name="backoffice_comment_remove")
     */
    public function commentRemove(Comment $comment){
        $em = $this->getDoctrine()->getManager();
        $em->remove($comment);
        $em->flush();
        $this->addFlash("success", "Article supprimé");
        return $this->redirectToRoute("backoffice_comments");
    }

    /**
     * @Route("/reports/{entity}", name="backoffice_reports")
     */
    public function reportsList($entity, Request $request){
        $query = $this->getDoctrine()->getRepository(Report::class)->findBy(["entity" => $entity], ["id" => "DESC"]);
        $paginator  = $this->get('knp_paginator');
        $reports = $paginator->paginate($query, $request->query->getInt('page', 1), 20);

        return $this->render('backoffice/Reports/index.html.twig', [
            "reports" => $reports,
            "entity" => $entity
        ]);
    }

    /**
     * @Route("/reports", name="backoffice_reports_all")
     */
    public function reports(Request $request)
    {
        $query = $this->getDoctrine()->getRepository(Report::class)->findBy(["id" => "DESC"]);
        $paginator = $this->get('knp_paginator');
        $reports = $paginator->paginate($query, $request->query->getInt('page', 1), 20);

        return $this->render('backoffice/Reports/all.html.twig', [
            "reports" => $reports
        ]);
    }

    /**
     * @Route("/report/edit/{id}", name="backoffice_report_edit")
     */
    public function reportEdit(Request $request, Report $report){

        $form = $this->createFormBuilder($report)
            ->add("reason", TextareaType::class)
            ->add("url", UrlType::class)
            ->add("entity", HiddenType::class)
            ->add("identifier", HiddenType::class)
            ->getForm()
            ->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()){
            $this->getDoctrine()->getManager()->flush();
            $this->addFlash("success", "Signalement modifié");
            $em = $this->getDoctrine()->getManager();
            $em->persist($report);
            $em->flush();
            return $this->redirectToRoute("backoffice_report_edit", ["id" => $report->getId()]);
        }

        return $this->render('backoffice/Reports/edit.html.twig', [
            "report" => $report,
            "form" => $form->createView()
        ]);
    }

    /**
     * @Route("/report/remove/{id}", name="backoffice_report_remove")
     */
    public function reportRemove(Report $report){
        $em = $this->getDoctrine()->getManager();
        $em->remove($report);
        $em->flush();
        $this->addFlash("success", "Signalement supprimé");
        return $this->redirectToRoute("backoffice_reports_all");
    }

    /**
     * @Route("/ranks", name="backoffice_forum_ranks")
     */
    public function ranks(Request $request)
    {
        $query = $this->getDoctrine()->getRepository(ForumRank::class)->findAll();
        $paginator = $this->get('knp_paginator');
        $ranks = $paginator->paginate($query, $request->query->getInt('page', 1), 20);

        return $this->render('backoffice/forum/ranks/index.html.twig', [
            "ranks" => $ranks
        ]);
    }

    /**
     * @Route("/rank/add", name="backoffice_forum_rank_add")
     */
    public function rankAdd(Request $request)
    {
        $rank = new ForumRank();
        $form = $this->createForm(ForumRankType::class, $rank)->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()){
            $em = $this->getDoctrine()->getManager();
            $em->persist($rank);
            $em->flush();
            $this->addFlash("success", "Rang ajouté avec succès.");
            return $this->redirectToRoute("backoffice_forum_ranks");
        }
        return $this->render('backoffice/forum/ranks/add.html.twig', [
            "form" => $form->createView()
        ]);
    }

    /**
     * @Route("/rank/edit/{id}", name="backoffice_forum_rank_edit")
     */
    public function rankEdit(Request $request, ForumRank $rank)
    {
        $form = $this->createForm(ForumRankType::class, $rank)->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()){
            $this->getDoctrine()->getManager()->flush();
            $this->addFlash("success", "Votre modification a été effectuée.");
            return $this->redirectToRoute("backoffice_forum_rank_edit");
        }
        return $this->render('backoffice/forum/ranks/edit.html.twig', [
            "form" => $form->createView()
        ]);
    }

    /**
     * @Route("/rank/remove/{id}", name="backoffice_forum_rank_remove")
     */
    public function rankRemove(ForumRank $rank){
        $em = $this->getDoctrine()->getManager();
        $em->remove($rank);
        $em->flush();
        $this->addFlash("success", "Rang supprimé");
        return $this->redirectToRoute("backoffice_forum_ranks");
    }

}
