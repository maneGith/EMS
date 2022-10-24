<?php

namespace AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use AppBundle\Entity\User;
use FOS\UserBundle\Model\UserManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class LoginController extends Controller
{
    private $userManager;
    public function __construct(UserManagerInterface $userManager)
    {
        $this->userManager = $userManager;
    }
    /**
     * @Route("/login", name="login")
     */
    public function loginAction(Request $request, AuthenticationUtils $authenticationUtils)
    {
        $errors=$authenticationUtils->getLastAuthenticationError();
        $LastUserName=$authenticationUtils->getLastUsername();

        return $this->render('default/login.html.twig', array(
            'errors'=>$errors,
            'username'=>$LastUserName,
        ));
    }


    /**
     * @Route("/logout", name="logout")
     */
    public function logoutAction()
    {

    }

    /**
     * @Route("/param/bur", name="param_bur")
     */
    public function paramBurAction()
    {
        $em = $this->getDoctrine()->getManager();

        $agences = $em->getRepository('AppBundle:Agence')->findByAgences();

        return $this->render('param/bureau.html.twig', array(
            'agences'=>$agences,
        ));
    }
    

    /**
     * @Route("/param/bur/user/pwd", name="param_bur_user-pwd")
     */
    public function paramBurUserPWDAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $newpass1=$request->get('newpass1');
        $newpass2=$request->get('newpass2');
        $user_id=$request->get('user');

        $user = $em->getRepository('AppBundle:Utilisateur')->findOneById($user_id)[0];
        $nom_agence='';
        if($user){
            $nom_agence=  $user->getAgence()->getNom();
        }
        //var_dump($nom_agence);die();
        $msg='';
        $error='';
        if($newpass1==null && $newpass2==null){
            $msg="Le mot de passe ne peut-être null   !";
            $error='2';
        }elseif($newpass1!=$newpass2){
            $msg="Les mots de passe ne sont pas conformes  !";
            $error='3';
        }else{
            $encoderService = $this->container->get('security.password_encoder');
            $newpass1=$encoderService->encodePassword($user, $newpass1);
            $user->setPassword($newpass1);
            $this->userManager->updateUser($user);
            $msg="La modification s'est effectuée avec succès !";
            $error='1';
        }

        $etatpwd= $error;
        //var_dump($newpass1);die();
//        return $this->render('param/info.html.twig', array(
//            'msg'=>$msg,
//            'nom_agence'=>$nom_agence,
//            'error'=>$error
//        ));
        
        return $this->redirectToRoute('user_edit', array('id' => $user_id,
              'etatpwd'=>$etatpwd
            ));
    }

}
