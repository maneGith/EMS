<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Depeche;
use AppBundle\Entity\DepecheContient;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * Depeche controller.
 *
 * @Route("depeche")
 */
class DepecheController extends Controller
{
    /**
     * Lists all depeche entities.
     *
     * @Route("/", name="depeche_index")
     * @Method({"GET", "POST"})
     */
    public function indexAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();

         $agence=$this->getUser()->getAgence();
          //Date de depeche
            date_default_timezone_set('UTC');
            $date = date('d/m/Y');
            //$depeche->setJournee($date)
            $annee= '%/'.substr($date, 6);
        
        //$depeches = $em->getRepository('AppBundle:Depeche')->findAll();
        
        $depeches = $em->getRepository('AppBundle:Depeche')->findByIdAgenceAnnee($agence, $annee);
        
        $depeche = new Depeche();
        $form = $this->createForm('AppBundle\Form\DepecheType', $depeche);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            
             //Date de depeche
            date_default_timezone_set('UTC');
            $date = date('d/m/Y');

            //$depeche->setJournee($date);

            $annee= '%/'.substr($date, 6);
             // var_dump($annee);die();
            $agence=$this->getUser()->getAgence();
            $depeche->setAgence($agence);  

             //
            //Numero de depeche
             $em = $this->getDoctrine()->getManager();
             $depeches = $em->getRepository('AppBundle:Depeche')->findByIdAgenceAnnee($agence, $annee);

             //var_dump(count($depeches));die();

            $nd=count($depeches)+1;
            if(strlen($nd)==1){
                $nd= '000'.$nd;
            }elseif(strlen($nd)==2){
                $nd= '00'.$nd;
            }elseif(strlen($nd)==3){
                $nd= '0'.$nd;
            }
            $depeche->setNumero($nd); 
            //Etat
            $depeche->setEtat('En fermeture'); 


            $em->persist($depeche);
            $em->flush();
            return $this->redirectToRoute('depeche_edit', array('id' => $depeche->getId()));
        }

        return $this->render('depeche/index.html.twig', array(
            'depeches' => $depeches,
            'depeche' => $depeche,
            'form' => $form->createView(),
        ));
    }
    
    
    
    /**
     * Lists all depeche entities.
     *
     * @Route("/historique", name="depeche_historique")
     * @Method({"GET", "POST"})
     */
    public function historiqueAction(Request $request)
    {
            $em = $this->getDoctrine()->getManager();
            $agence=$this->getUser()->getAgence();
            //Date de depeche
            date_default_timezone_set('UTC');
            $date = date('d/m/Y');
            //$depeche->setJournee($date)
            $annee= '%/'.substr($date, 6);
            $depeches = $em->getRepository('AppBundle:Depeche')->findByIdAgenceAnneeTransmis($agence, $annee);
            
            return $this->render('depeche/historique.html.twig', array(
            'depeches' => $depeches,
        )); 
    }

   

    /**
     * Displays a form to edit an existing depeche entity.
     *
     * @Route("/{id}/edit", name="depeche_edit")
     * @Method({"GET", "POST"})
     */
    public function editAction(Request $request, Depeche $depeche)
    {
        $deleteForm = $this->createDeleteForm($depeche);
        $editForm = $this->createForm('AppBundle\Form\DepecheType', $depeche);
        $editForm->handleRequest($request);

        if ($editForm->isSubmitted() && $editForm->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('depeche_edit', array('id' => $depeche->getId()));
        }

        return $this->render('depeche/edit.html.twig', array(
            'depeche' => $depeche,
            'edit_form' => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ));
    }
    
    
    /**
     *
     * @Route("/{id}/transmis", name="depeche_transmis")
     * @Method({"GET", "POST"})
     */
    public function transmettreAction(Depeche $depeche)
    {
            $em = $this->getDoctrine()->getManager();
            //Etat
            $depeche->setEtat('Transmis'); 
            $em->persist($depeche);
            $em->flush();
            return $this->redirectToRoute('depeche_index');
    }
    
    /**
     *
     * @Route("/{id}/enfermeture", name="depeche_enfermeture")
     * @Method({"GET", "POST"})
     */
    public function enfermetureAction(Depeche $depeche)
    {
            $em = $this->getDoctrine()->getManager();
            //Etat
            $depeche->setEtat('En fermeture'); 
            $em->persist($depeche);
            $em->flush();
            return $this->redirectToRoute('depeche_index');
    }
    
    /**
     *
     * @Route("/{id}/chargement", name="depeche_chargement")
     * @Method({"GET", "POST"})
     */
    public function chargementAction(Depeche $depeche)
    {
        
        if($this->get('security.authorization_checker')->isGranted('ROLE_AGENTCABINE')){
              $em = $this->getDoctrine()->getManager();
             
              $date=date('d/m/Y');
              $periode='%'.substr($date, 2);
              $periode2=substr($date, 3,2)-1;
              $periode2='%/0'.$periode2.substr($date, 5);
              //var_dump($periode);die();
              
              
              $agence=$this->getUser()->getAgence();
              
            
              //Recuperation type depeche
              $type_depeche=$depeche->getType();
            
               
               if($type_depeche=='dépêche des envois nationaux')
                   {
                     $envois = $em->getRepository('AppBundle:Envoi')->findByMoisAgenceDepeche($agence, $periode, $periode2, 'National');
                   }
               else {
                    $envois = $em->getRepository('AppBundle:Envoi')->findByMoisAgenceDepeche($agence, $periode, $periode2, 'International');
                }
              
             
              $tab=array();
              
              //$contient= $em->getRepository('AppBundle:DepecheContient')->findOneByIdEnvoi(33);
              // var_dump(count($contient));die();
               for ($i=0; $i<count($envois); $i++){
                   $contient= $em->getRepository('AppBundle:DepecheContient')->findOneByIdEnvoi($envois[$i]->getId());
                   if(count($contient)==0){
                       $tab[count($tab)]=$envois[$i];
                   }
               }
              
                $envois =   $tab;
                $contenudepeche=$em->getRepository('AppBundle:DepecheContient')->findByIdDepeche($depeche);
                        
               //var_dump($contient);die();
               
             return $this->render('depeche/chargement.html.twig', array(
            'depeche' => $depeche,
            'envois' => $envois,
            'contenudepeche' => $contenudepeche
        ));
             
             
             }else{
            return $this->redirectToRoute('homepage');
        }
    }
    
    
    
    /**
     *
     * @Route("/{id}/manifeste", name="depeche_manifeste")
     * @Method({"GET", "POST"})
     */
    public function manifesteAction(Depeche $depeche)
    {
              $em = $this->getDoctrine()->getManager();
             
             
              $agence=$this->getUser()->getAgence();
              
            //Recuperation type depeche
              $type_depeche=$depeche->getType();
             
               
                        
                
                 if($type_depeche=='dépêche des envois nationaux')
                   {
                     $contenudepeche=$em->getRepository('AppBundle:DepecheContient')->findByIdDepeche($depeche);
                   }
               else {
                     $contenudepeche=$em->getRepository('AppBundle:DepecheContient')->findByIdDepecheInt($depeche);
                }
                
                
                
                 $gauchemanifeste=array();
                 $droitemanifeste=array();
                 
                 for ($i=0; $i<count($contenudepeche); $i++){
                   
                   if($i<=29){
                       $gauchemanifeste[count($gauchemanifeste)]=$contenudepeche[$i];
                   }else{
                       $droitemanifeste[count($droitemanifeste)]=$contenudepeche[$i];
                   }
               }
                  $gauche=count($gauchemanifeste)+1;
                  $droite=count($droitemanifeste)+1;
               //var_dump($contenudepeche);die();
               
             return $this->render('depeche/manifeste.html.twig', array(
            'depeche' => $depeche,
            'gauchemanifeste' => $gauchemanifeste,
            'gauche' => $gauche,
            'droitemanifeste' => $droitemanifeste,
            'droite' => $droite,
        ));
    }
    
    /**
     *
     * @Route("/placement", name="placer_depeche")
     * @Method({"GET", "POST"})
     */
    public function placerAction(Request $request)
    {
              $em = $this->getDoctrine()->getManager();
              
              $depeche=$request->get('id');
              //$depeche=(int)$depeche;
               //var_dump($depeche);die();
              $depeche = $em->getRepository('AppBundle:Depeche')->findOneById($depeche)[0];
             // var_dump($depeche->getId());die();
                //findOneById($envoi)
              $data = $request->request->get('data');
              
              $envois=explode('=', $data);
              //$envois=$data.split('=');
             
             // var_dump($envois[1]);die();
              
              for ($i=0; $i<count($envois); $i++){

                $depecheenvoi=new DepecheContient();
                $id_envoi=(int)$envois[$i];
                $envoi = $em->getRepository('AppBundle:Envoi')->findOneById($id_envoi)[0];
                
                //affectation depe che
                $depecheenvoi->setDepeche($depeche);
                 //affactation envoi
                $depecheenvoi->setEnvoi($envoi);
                  
                   //var_dump($depeche_envoi);die();
                $em->persist($depecheenvoi);
                $em->flush();  
               
                   //var_dump($depeche);die();
              }
              
            
            
              
               //var_dump($envois);die();
               return new JsonResponse([
       'info'=> $data
       ]);
          
    }

    /**
     * Deletes a depeche entity.
     *
     * @Route("/{id}", name="depeche_delete")
     * @Method("DELETE")
     */
    public function deleteAction(Request $request, Depeche $depeche)
    {
        $form = $this->createDeleteForm($depeche);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->remove($depeche);
            $em->flush();
        }

        return $this->redirectToRoute('depeche_index');
    }

    /**
     * Creates a form to delete a depeche entity.
     *
     * @param Depeche $depeche The depeche entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm(Depeche $depeche)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('depeche_delete', array('id' => $depeche->getId())))
            ->setMethod('DELETE')
            ->getForm()
        ;
    }
    
    
    
    /**
     * Deletes a DepecheContient entity.
     *
     * @Route("/{id}", name="depechecontient_delete")
     */
    public function deletedepechecontientAction(DepecheContient $depechecontient)
    {
            $em = $this->getDoctrine()->getManager();
            $id=$depechecontient->getDepeche()->getId();
            $em->remove($depechecontient);
            $em->flush();
            return $this->redirectToRoute('depeche_chargement', array('id' =>$id ));
    }
}
