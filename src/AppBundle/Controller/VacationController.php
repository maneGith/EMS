<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Bordereau;
use AppBundle\Entity\Vacation;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;

/**
 * Vacation controller.
 *
 * @Route("agent/vacation")
 */
class VacationController extends Controller
{
    /**
     * Lists all vacation entities.
     *
     * @Route("/", name="vacation_index")
     *
     *
     * @Method({"GET", "POST"})
     */
    public function indexAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $date=date('d/m/Y');
        $annee_cours=substr($date, 6);

        $msg = $request->get('msg');
        $getmois = $request->get('mois');
        if($getmois!=null){
            $date='00/'.$getmois;

        }else{
            $date=date('d/m/Y');
        }
        
        //Annee conditionnelle pour affichage
        $anneteste=substr($date, 6);
        
        $mois=substr($date, 3);
        $session=new Session();


        $date='%'.substr($date, 2);

        $vacationslist = $em->getRepository('AppBundle:Vacation')->findByIdUserMois($this->getUser()->getId(), $date);


        if(!$msg){
            // set and get session attributes
            $session->set('msg', '');
        }
        $vacations=array();
        for ($i=0; $i<count($vacationslist); $i++){
            $recup_infosvacations['vacation']= $vacationslist[$i];
            $id_vacation= $vacationslist[$i]->getId();

            $bordereau = $em->getRepository('AppBundle:Bordereau')->findByIdVacation($id_vacation);
           // var_dump($id_vacation);die();
            $recup_infosvacations['bordereaux']= $bordereau;

            $vacations[$i]=$recup_infosvacations;
        }

        //Creation
        if($this->get('security.authorization_checker')->isGranted('ROLE_AGENTGUICHET')){
            $vacation = new Vacation();
            if ($this->getUser()) {
                $vacation->setUtilisateur($this->getUser());
                $vacation->setAgence($this->getUser()->getAgence());
            }
            $form = $this->createForm('AppBundle\Form\VacationType', $vacation);
            $form->handleRequest($request);
            $em = $this->getDoctrine()->getManager();


            $date = $request->get('datepicker');
            $annee=substr($date, 6);
            $moisreq='%'.substr($date, 3);
            //$vacations = $em->getRepository('AppBundle:Vacation')->findAllByUserAnnee($this->getUser()->getId(),$annee);

            $session=new Session();
            $utilisateur=$this->getUser();
            //Verification de la vacation du jour
            $vacations_jour = $em->getRepository('AppBundle:Vacation')->findByIdUserDate($utilisateur, $date);
            if($vacations_jour){
                // set and get session attributes
                $session->set('msg', "Vous avez déja créé la vacation journée du "."$date");
                return $this->redirectToRoute('vacation_index', array('msg' => '1'));
            }

            $vacationstab = $em->getRepository('AppBundle:Vacation')->findByMoisAgence($moisreq, $this->getUser()->getAgence()->getId());
            $numvacation=count($vacationstab)+1;
            //var_dump($numvacation);die();
            $vacation->setNumvacation($numvacation);
            if(strlen($numvacation)==1){
                $numvacation= '00'.$numvacation;
            }elseif(strlen($numvacation)==2){
                $numvacation= '0'.$numvacation;
            }elseif(strlen($numvacation)==3){
                $numvacation= $numvacation;
            }

            $vacation->setAnnee($annee);
            $vacation->setJournee($date);
            if ($form->isSubmitted() && $form->isValid()) {

                if(strlen($date)<10){
                    // set and get session attributes
                    $session->set('msg', "La date est incorrecte");
                    return $this->redirectToRoute('vacation_index', array('msg' => '1'));
                }

                if($date==null){
                    return $this->redirectToRoute('vacation_new');
                }

                $debutheure = $request->get('debutheure');
                $debutminute = $request->get('debutminute');
                $debut=$debutheure.':'.$debutminute;
                $vacation->setDebut($debut);

                $finheure = $request->get('finheure');
                $finminute = $request->get('finminute');
                $fin=$finheure.':'.$finminute;
                $vacation->setFin($fin);

                //var_dump($fin);die();

                $em = $this->getDoctrine()->getManager();
                $em->persist($vacation);
                $em->flush();

                return $this->redirectToRoute('vacation_index');
            }

            
        }
        //Fin

        if($this->get('security.authorization_checker')->isGranted('ROLE_AGENTGUICHET')){
            
            
            
            
            if($anneteste!=$annee_cours){
               return $this->render('vacation/indexhistorique.html.twig', array(
                'vacations' => $vacations,
                'annee_cours' => $annee_cours,
                'mois' => $mois,
                'vacationelement' => $vacation,
                'form' => $form->createView(),
                'date' => $date,
                'numvacation' => $numvacation,
            ));
                
            }else{
               return $this->render('vacation/index.html.twig', array(
                'vacations' => $vacations,
                'annee_cours' => $annee_cours,
                'mois' => $mois,
                'vacationelement' => $vacation,
                'form' => $form->createView(),
                'date' => $date,
                'numvacation' => $numvacation,
            ));
            }
            
            
        }else{
            return $this->redirectToRoute('homepage');
        }

    }

    /**
     * Creates a new vacation entity.
     *
     * @Route("/new", name="vacation_new")
     * @Method({"GET", "POST"})
     */
    public function newAction(Request $request)
    {




        if($this->get('security.authorization_checker')->isGranted('ROLE_AGENTGUICHET')){
            $vacation = new Vacation();
            if ($this->getUser()) {
                $vacation->setUtilisateur($this->getUser());
                $vacation->setAgence($this->getUser()->getAgence());
            }
            $form = $this->createForm('AppBundle\Form\VacationType', $vacation);
            $form->handleRequest($request);
            $em = $this->getDoctrine()->getManager();


            $date = $request->get('datepicker');
            $annee=substr($date, 6);
            $mois='%'.substr($date, 3);
            //$vacations = $em->getRepository('AppBundle:Vacation')->findAllByUserAnnee($this->getUser()->getId(),$annee);

            $session=new Session();
            $utilisateur=$this->getUser();
            //Verification de la vacation du jour
            $vacations_jour = $em->getRepository('AppBundle:Vacation')->findByIdUserDate($utilisateur, $date);
            if($vacations_jour){
                // set and get session attributes
                $session->set('msg', "Vous avez déja créé la vacation journée du "."$date");
                return $this->redirectToRoute('vacation_index', array('msg' => '1'));
            }

            $vacations = $em->getRepository('AppBundle:Vacation')->findByMoisAgence($mois, $this->getUser()->getAgence()->getId());
            $numvacation=count($vacations)+1;
            //var_dump($numvacation);die();
            $vacation->setNumvacation($numvacation);
            if(strlen($numvacation)==1){
                $numvacation= '00'.$numvacation;
            }elseif(strlen($numvacation)==2){
                $numvacation= '0'.$numvacation;
            }elseif(strlen($numvacation)==3){
                $numvacation= $numvacation;
            }

            $vacation->setAnnee($annee);
            $vacation->setJournee($date);
            if ($form->isSubmitted() && $form->isValid()) {

                if(strlen($date)<10){
                    // set and get session attributes
                    $session->set('msg', "La date est incorrecte");
                    return $this->redirectToRoute('vacation_index', array('msg' => '1'));
                }

                if($date==null){
                    return $this->redirectToRoute('vacation_new');
                }

                $debutheure = $request->get('debutheure');
                $debutminute = $request->get('debutminute');
                $debut=$debutheure.':'.$debutminute;
                $vacation->setDebut($debut);

                $finheure = $request->get('finheure');
                $finminute = $request->get('finminute');
                $fin=$finheure.':'.$finminute;
                $vacation->setFin($fin);

                //var_dump($fin);die();

                $em = $this->getDoctrine()->getManager();
                $em->persist($vacation);
                $em->flush();

                return $this->redirectToRoute('vacation_index');
            }

            return $this->render('vacation/new.html.twig', array(
                'vacation' => $vacation,
                'form' => $form->createView(),
                'date' => $date,
                'numvacation' => $numvacation,
            ));
        }else{
            return $this->redirectToRoute('homepage');
        }


    }



    /**
     * Displays a form to edit an existing vacation entity.
     *
     * @Route("/{id}/edit", name="vacation_edit")
     * @Method({"GET", "POST"})
     */
    public function editAction(Request $request, Vacation $vacation)
    {
        $deleteForm = $this->createDeleteForm($vacation);
        $editForm = $this->createForm('AppBundle\Form\VacationType', $vacation);
        $editForm->handleRequest($request);

        if ($editForm->isSubmitted() && $editForm->isValid()) {

            $debutheure = $request->get('debutheure');
            $debutminute = $request->get('debutminute');
            $debut=$debutheure.':'.$debutminute;
            $vacation->setDebut($debut);

            $finheure = $request->get('finheure');
            $finminute = $request->get('finminute');
            $fin=$finheure.':'.$finminute;
            $vacation->setFin($fin);

            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('vacation_edit', array('id' => $vacation->getId()));
        }

        $debut=$vacation->getDebut();
       // var_dump($debut);die();
        $debutheure =substr($debut,0,2);
        $debutminute = substr($debut,3);


        $fin=$vacation->getFin();
        $finheure = substr($fin,0,2);
       // var_dump($finheure);die();
        $finminute = substr($fin,3);


        return $this->render('vacation/edit.html.twig', array(
            'vacation' => $vacation,
            'edit_form' => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
            'debutheure' => $debutheure,
            'debutminute' => $debutminute,
            'finheure' => $finheure,
            'finminute' => $finminute,
        ));
    }
    
    
    /**
     * 
     *
     * @Route("/historique", name="vacation_historique")
     */
    public function historiqueAction(Request $request)
    {
         $date=date('d/m/Y');
         $annee_prec=substr($date,6)-1;
         $annee_preprec = $annee_prec-1;
         //var_dump($annee_preprec);die();

        return $this->render('vacation/historique.html.twig', array(
            'anneeprec' => $annee_prec,
            'anneepreprec' => $annee_preprec
            ));
    }

    /**
     * Deletes a vacation entity.
     *
     * @Route("/{id}", name="vacation_delete")
     */
    public function deleteAction(Request $request, Vacation $vacation)
    {
        //$form = $this->createDeleteForm($vacation);
//
       // if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->remove($vacation);
            $em->flush();
        //}

        return $this->redirectToRoute('vacation_index');
    }

    /**
     * Deletes a bordereau entity.
     *
     * @Route("/bord/{id}", name="bordereau_delete")
     */
    public function deleteBLAction(Request $request, Bordereau $bordereau)
    {
        //$form = $this->createDeleteForm($vacation);
//
        // if ($form->isSubmitted() && $form->isValid()) {
        $em = $this->getDoctrine()->getManager();
        $em->remove($bordereau);
        $em->flush();
        //}

        return $this->redirectToRoute('vacation_index');
    }

    /**
     * Creates a form to delete a vacation entity.
     *
     * @param Vacation $vacation The vacation entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm(Vacation $vacation)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('vacation_delete', array('id' => $vacation->getId())))
            ->setMethod('DELETE')
            ->getForm()
        ;
    }
}
