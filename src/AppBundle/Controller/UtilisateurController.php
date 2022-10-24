<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Agence;
use AppBundle\Entity\Utilisateur;
use FOS\UserBundle\Model\UserManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Utilisateur controller.
 *
 * @Route("admin/user")
 */
class UtilisateurController extends Controller
{
    private $userManager;
    private $containerInterface;
    public function __construct(UserManagerInterface $userManager, ContainerInterface $containerInterface)
    {

        $this->userManager = $userManager;
        $this->containerInterface = $containerInterface;
    }

    /**
     * Lists all utilisateur entities.
     *
     * @Route("/", name="user_index")
     * @Method("GET")
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();
        //$utilisateurs = $em->getRepository('AppBundle:Utilisateur')->findByUsers();
        $profils = $em->getRepository('AppBundle:Utilisateur')->findByProfil();


        $utilisateurs=array();
        $recup_infosutilisateur=array();

        for ($i=0; $i<count($profils); $i++){
            $recup_infosutilisateur['profil']= $profils[$i]['profil'];
            $profil=$profils[$i]['profil'];


            $users = $em->getRepository('AppBundle:Utilisateur')->findByProfilAgenceNom($profil);
            $recup_infosutilisateur['users']= $users;
            $utilisateurs[$i]=$recup_infosutilisateur;
        }


        return $this->render('utilisateur/index.html.twig', array(
            'utilisateurs' => $utilisateurs,
        ));
    }

    /**
     * Creates a new utilisateur entity.
     *
     * @Route("/new", name="user_new")
     * @Method({"GET", "POST"})
     */
    public function newAction(Request $request)
    {
        $utilisateur = new Utilisateur();
        $password=$request->get('password');
        $utilisateur->setEnabled(true);
        $utilisateur->setPassword($password);
        $form = $this->createForm('AppBundle\Form\UtilisateurType', $utilisateur);
        $form->handleRequest($request);

        $em = $this->getDoctrine()->getManager();
        $rpsu = $em->getRepository('AppBundle:Utilisateur');
        $utilisateurs = $rpsu->findByUsers();

        if ($form->isSubmitted() && $form->isValid()) {
            $utilisateur->setAgence($this->idDirection());
            $error="";
            if (!$this->isValidEmail($utilisateur->getEmail()))
            {
                $error="L'email est déja utilisé";
                return $this->render('utilisateur/new.html.twig', array(
                    'user' => $utilisateur,
                    'form' => $form->createView(),
                    'users' => $utilisateurs,
                    'error' => $error,
                ));
            }else{

                if (!$this->isValidUsername($utilisateur->getUsername()))
                {
                    $error="Le username est déja utilisé";
                    return $this->render('utilisateur/new.html.twig', array(
                        'user' => $utilisateur,
                        'form' => $form->createView(),
                        'users' => $utilisateurs,
                        'error' => $error,
                    ));
                }else{

                    //Vérification confirmation du mot de passe//

                    $firstpassword=$utilisateur->getPassword();
                    $passwordrepeated=$request->get('passwordrepeated');
                    if ($passwordrepeated!==$firstpassword) {
                        $error="Erreur de confirmation de mot de passe";
                        return $this->render('utilisateur/new.html.twig', array(
                            'user' => $utilisateur,
                            'form' => $form->createView(),
                            'users' => $utilisateurs,
                            'error' => $error,
                        ));
                    }
                }

            }





            //Enregistrement  dans la base de données
            $password=$utilisateur->getPassword();
            $encoder=$this->container->get('security.password_encoder');
            $encoded= $encoder->encodePassword($utilisateur, $password);
            $utilisateur->setPassword($encoded);
            $profil= $utilisateur->getProfil();

            $role='ROLE_'.$profil;

            $roles=[$role];
            $utilisateur->setRoles($roles);
           // var_dump($encoded);die();
            $this->userManager->updateUser($utilisateur);
            return $this->redirectToRoute('user_edit', array('id' => $utilisateur->getId()));

        }

        return $this->render('utilisateur/new.html.twig', array(
            'utilisateur' => $utilisateur,
            'form' => $form->createView(),
            'users' => $utilisateurs,
        ));
    }




    /**
     * Displays a form to edit an existing utilisateur entity.
     *
     * @Route("/{id}/edit", name="user_edit")
     * @Method({"GET", "POST"})
     */
    public function editAction(Request $request, Utilisateur $utilisateur)
    {

        $editForm = $this->createForm('AppBundle\Form\UtilisateurType', $utilisateur);
        $editForm->handleRequest($request);

        $profilageence=array("DGEMS"=>"DG","DEXEMS" => "DG","DAFEMS" => "DG","CONTROLE" => "DG",
            "RECOUVREMENT" => "DG","CHEFDAGENCE" => "AG","AGENTGUICHET"=>"AG","AGENTCABINE"=>"AG","ADMIN"=>"DG");

        //Pour la recupération des agences

       // $em = $this->getDoctrine()->getManager();
       // $em->flush();
        $agences = '';//$em->getRepository('AppBundle:Agence')->findAll();
        $validmail=$this->isValidEmailId($utilisateur->getEmail(),$utilisateur->getId());
        $validusername=$this->isValidUsernameId($utilisateur->getUsername(),$utilisateur->getId());

       $etatpwd=$request->get('etatpwd');

        //$error=$this->get('error');

        $session=$this->get('session');
        if($request->get('error')==null)
        {
            $session->set('error','');
        }
        //var_dump($session->get('machin'));die();
        if ($editForm->isSubmitted()) {

            if($editForm->isValid())
            {
                $etat = $request->get('etat');
                $utilisateur->setEnabled($etat);
                $statut = $utilisateur->getAgence()->getStatut();
                if($statut=='PD' or $statut=='PR'){
                    $statut='AG';
                }
                $profil=$utilisateur->getProfil();


                if (!$validmail)
                {

                    //flash
                    $session->set('error','Cet email est déja utilisé');
                    $error='1';

                    return $this->redirectToRoute('user_edit', array('id' => $utilisateur->getId(),
                           'error' => $error));
                }else{
                    if (!$validusername)
                    {

                        $session->set('error','Ce username est déja utilisé');
                        $error='1';

                        return $this->redirectToRoute('user_edit', array('id' => $utilisateur->getId(),
                            'error' => $error));
                    }}

                if($profilageence[$profil]!==$statut){

                    $message='';
                    if($statut=='DG'){
                        $message="Erreur de Profil/Agence, on ne peut pas affecter un ".$profil." à la Direction générale";
                    }else{
                        $message="Impossible d'affecter un ".$profil." à une agence";
                    }
                    $session->set('error',$message);
                    $error='1';

                    return $this->redirectToRoute('user_edit', array('id' => $utilisateur->getId(),
                        'error' => $error));


                }

                //Encodage du mot de passe de l'utilisateur
                //$encoder=$this->containerInterface->get('security.password_encoder');
                //$password= $encoder->encodePassword($utilisateur, $utilisateur->getPassword());
                //$utilisateur->setPassword($password);

                $role='ROLE_'.$profil;

                $roles=[$role];
                $utilisateur->setRoles($roles);

                 $this->getDoctrine()->getManager()->flush();

                return $this->redirectToRoute('user_edit', array('id' => $utilisateur->getId()));

            }else{


                $nom=$utilisateur->getNom();
                $nom=str_replace(' ','',$nom);

                if($nom==''){
                    $session->set('error','Le nom est obligatoire');
                    $error='1';
                    return $this->redirectToRoute('user_edit', array('id' => $utilisateur->getId(),
                        'error' => $error));
                }

            }
        }

        return $this->render('utilisateur/edit.html.twig', array(
            'user' => $utilisateur,
            'agences' => $agences,
            'edit_form' => $editForm->createView(),
            'etatpwd'=>$etatpwd
        ));
    }





    /**
     * Deletes a utilisateur entity.
     *
     * @Route("/{id}", name="user_delete")
     *
     */
    public function deleteAction(Utilisateur $utilisateur)
    {
        $em = $this->getDoctrine()->getManager();
        $em->remove($utilisateur);
        $em->flush();
        return $this->redirectToRoute('user_index');
    }



    /**
     * @param $email
     *
     * @return boolean
     */
    public function isValidEmail($email){
        $em = $this->getDoctrine()->getManager();

        $validEmail = $em->getRepository('AppBundle:Utilisateur')->findOneByEmail($email);

        if($validEmail){
            return false;
        }else{
            return true;
        }

    }

    /**
     * @param $email
     *
     * @return boolean
     */
    public function isValidEmailId($email, $id){
        $em = $this->getDoctrine()->getManager();

        $validEmail = $em->getRepository('AppBundle:Utilisateur')->findOneByEmailId($email, $id);

        if($validEmail){
            return false;
        }else{
            return true;
        }

    }


    /**
     * @param $username
     *
     * @return boolean
     */
    public function isValidUsername($username){
        $em = $this->getDoctrine()->getManager();

        $validUsername = $em->getRepository('AppBundle:Utilisateur')->findOneByUsername($username);

        if($validUsername){
            return false;
        }else{
            return true;
        }

    }

    /**
     * @param $username
     *
     * @return boolean
     */
    public function isValidUsernameId($username, $id){
        $em = $this->getDoctrine()->getManager();

        $validUsername = $em->getRepository('AppBundle:Utilisateur')->findOneByUsernameId($username, $id);

        if($validUsername){
            return false;
        }else{
            return true;
        }

    }

    /**
     *
     * @return Agence
     *
     */
    public function idDirection(){

        $em = $this->getDoctrine()->getManager();
        $direction = $em->getRepository('AppBundle:Agence')->findOneByDG();
        if($direction){
            return $direction[0];
        }else{
            $agence= new Agence();
            $agence->setNom('Direction générale');
            $agence->setLocalite('Dakar');
            $agence->setAdresse('Domaine Sodida');
            $agence->setStatut('DG');
            $agence->setCodeO('XX');
            $agence->setCodeR('XX');
            $em->persist($agence);
            $em->flush();
            $direction = $em->getRepository('AppBundle:Agence')->findOneByDG();
            return $direction[0];
        }


    }
}
