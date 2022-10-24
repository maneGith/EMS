<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Envoi;
use AppBundle\Entity\Bordereau;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;

/**
 * Envoi controller.
 *
 * @Route("agent/envoi")
 */
class EnvoiController extends Controller
{
    /**
     * Lists all envoi entities.
     *
     * @Route("/", name="envoi_index")
     * @Method("GET")
     */
    public function indexAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $id_bordereau = $request->get('id');

        if($id_bordereau==null){
            return $this->redirectToRoute('homepage');
        }


        $envois = $em->getRepository('AppBundle:Envoi')->findByBordereau($id_bordereau);





        $totaljour['nbenvoi']=count($envois);
        $totaljour['poids']=0;
        $totaljour['taxe']=0;
        $totaljour['tva']=0;

        $anterieur['nbenvoi']=0;
        $anterieur['poids']=0;
        $anterieur['taxe']=0;
        $anterieur['tva']=0;

        $totalgen['nbenvoi']=0;
        $totalgen['poids']=0;
        $totalgen['taxe']=0;
        $totalgen['tva']=0;

        if($envois){

            for($i=0; $i<count($envois); $i++)
            {

                $totaljour['poids']=$totaljour['poids']+$envois[$i]->getPoids();
                $totaljour['taxe']=$totaljour['taxe']+$envois[$i]->getTarif();
                $totaljour['tva']=$totaljour['tva']+$envois[$i]->getTva();
            }
        }



        $date=date('d/m/Y');
        $mois='%'.substr($date, 2);

        $bordereau=  $em->getRepository('AppBundle:Bordereau')->findOneById($id_bordereau)[0];

        //$id_user=$this->getUser()->getId();
        $id_user= $bordereau->getVacation()->getUtilisateur();
        $journee= $bordereau->getVacation()->getJournee();
       // var_dump($journee);die();
        $mois='%'.substr($journee, 2);
        $jj=substr($journee, 0, 2);
        $typeenvoi=$bordereau->getTypeenvoi();
        //var_dump($typeenvoi);die();


        $tabant = $em->getRepository('AppBundle:Envoi')->findTotalByMoisAgent($id_user, $typeenvoi, $mois, $journee);
      // var_dump($tabant);die();

        $anterieur['nbenvoi']=0;
        $anterieur['poids']=0;
        $anterieur['taxe']=0;
        $anterieur['tva']=0;
        if($tabant){
            //var_dump($totauxenvois);die();
            for($i=0; $i<count($tabant); $i++)
            {
            $anterieur['nbenvoi']=$anterieur['nbenvoi']+1;
            $anterieur['poids']=$anterieur['poids']+$tabant[$i]->getPoids();
            $anterieur['taxe']=$anterieur['taxe']+$tabant[$i]->getTarif();
            $anterieur['tva']=$anterieur['tva']+$tabant[$i]->getTva();
            }
        }



        $totalgen['nbenvoi']=$totaljour['nbenvoi'] + $anterieur['nbenvoi'];
        $totalgen['poids']=$totaljour['poids'] + $anterieur['poids'];
        $totalgen['taxe']=$totaljour['taxe'] + $anterieur['taxe'];
        $totalgen['tva']=$totaljour['tva'] + $anterieur['tva'];



        //Traitement bordereau pagination
        $nbenvoiBR=count($envois);
        $mod=$nbenvoiBR%11;
        $tabEnvois=array();
        $recupEnv=array();

        $reppois=0;
        $reptaxe=0;
        $reptva=0;

        $recupEnv['code']='';
        $recupEnv['id']='';
        $recupEnv['nomexp']='';
        $recupEnv['paysexp']='';
        $recupEnv['nomdest']='';
        $recupEnv['ville']='';
        $recupEnv['paysdest']='';
        $recupEnv['heure']='';

        $recupEnv['poids'] = '';
        $recupEnv['taxe'] = '';
        $recupEnv['tva'] = '';

        if($mod==0){
            $k=-1;
            for($j=1; $j<=$nbenvoiBR; $j++)
            {
                $i=$j-1;
                $reppois= $reppois + $envois[$i]->getPoids();
                $reptaxe=$reptaxe + $envois[$i]->getTarif();
                $reptva=$reptva + $envois[$i]->getTva();

                if( $j%11==0){


                    $recupEnv['nature']='ENR';
                    $recupEnv['code']=$envois[$i]->getCodeenvoi();
                    $recupEnv['id']=$envois[$i]->getId();

                    $recupEnv['nomexp']=$envois[$i]->getExpediteur()->getUsager()->getNom();


                    $recupEnv['paysexp']=$envois[$i]->getExpediteur()->getUsager()->getPays()->getName();
                    $recupEnv['nomdest']=$envois[$i]->getDestinataire()->getUsager()->getNom();
                    $recupEnv['ville']=$envois[$i]->getDestinataire()->getUsager()->getVille();
                    $recupEnv['paysdest']=$envois[$i]->getDestinataire()->getUsager()->getPays()->getName();
                    $recupEnv['heure']=$envois[$i]->getHeure();

                    $recupEnv['poids']=$envois[$i]->getPoids();
                    $recupEnv['taxe']=$envois[$i]->getTarif();
                    $recupEnv['tva']=$envois[$i]->getTva();
                    $k= $k+1;
                    $tabEnvois[$k]=$recupEnv;


                    $recupEnv['nature']='RP';
                    $recupEnv['code']='';
                    $recupEnv['id']='';
                    $recupEnv['nomexp']='';
                    $recupEnv['paysexp']='';
                    $recupEnv['nomdest']='';
                    $recupEnv['ville']='';
                    $recupEnv['paysdest']='';
                    $recupEnv['heure']='';

                    $recupEnv['poids'] = $reppois;
                    $recupEnv['taxe'] = $reptaxe;
                    $recupEnv['tva'] = $reptva;
                    $k= $k+1;
                    $tabEnvois[$k]=$recupEnv;
                }else{
                    $recupEnv['nature']='ENR';
                    $recupEnv['code']=$envois[$i]->getCodeenvoi();
                    $recupEnv['id']=$envois[$i]->getId();

                    $recupEnv['nomexp']=$envois[$i]->getExpediteur()->getUsager()->getNom();

                    $recupEnv['paysexp']=$envois[$i]->getExpediteur()->getUsager()->getPays()->getName();
                    $recupEnv['nomdest']=$envois[$i]->getDestinataire()->getUsager()->getNom();
                    $recupEnv['ville']=$envois[$i]->getDestinataire()->getUsager()->getVille();
                    
                    
                    if( $envois[$i]->getDestinataire()->getUsager()->getPays()==null){
                              var_dump($recupEnv['code']);die(); 
                         }
                    
                    $recupEnv['paysdest']=$envois[$i]->getDestinataire()->getUsager()->getPays()->getName();
                    $recupEnv['heure']=$envois[$i]->getHeure();

                    $recupEnv['poids']=$envois[$i]->getPoids();
                    $recupEnv['taxe']=$envois[$i]->getTarif();
                    $recupEnv['tva']=$envois[$i]->getTva();
                    $k= $k+1;
                    $tabEnvois[$k]=$recupEnv;
                }
            }

            $i=count($tabEnvois);
            $recupEnv['nature']='TJ';
            $recupEnv['nomdest']=$totaljour['nbenvoi'];
            $recupEnv['poids']=$totaljour['poids'];
            $recupEnv['taxe']=$totaljour['taxe'];
            $recupEnv['tva']=$totaljour['tva'];

            $tabEnvois[$i]=$recupEnv;


            $i=count($tabEnvois);
            $recupEnv['nature']='TA';
            $recupEnv['nomdest']=$anterieur['nbenvoi'];
            $recupEnv['poids']=$anterieur['poids'];
            $recupEnv['taxe']=$anterieur['taxe'];
            $recupEnv['tva']=$anterieur['tva'];

            $tabEnvois[$i]=$recupEnv;

            $i=count($tabEnvois);
            $recupEnv['nature']='TG';
            $recupEnv['nomdest']=$totalgen['nbenvoi'];
            $recupEnv['poids']=$totalgen['poids'];
            $recupEnv['taxe']=$totalgen['taxe'];
            $recupEnv['tva']=$totalgen['tva'];

            $tabEnvois[$i]=$recupEnv;


            //var_dump($mod.'n');die();

        }else{

            //Traitement des envois intermediares

            if($nbenvoiBR>11){


                $nbinter=$nbenvoiBR-$mod;

                $k=-1;
                for($j=1; $j<=$nbinter; $j++)
                {

                    $i=$j-1;
                    $reppois= $reppois + $envois[$i]->getPoids();
                    $reptaxe=$reptaxe + $envois[$i]->getTarif();
                    $reptva=$reptva + $envois[$i]->getTva();

                    if( $j%11==0){

                        if($j!=11){
                            //var_dump('dd');die();
                        }

                        $recupEnv['nature']='ENR';

                        $recupEnv['code']=$envois[$i]->getCodeenvoi();
                        $recupEnv['id']=$envois[$i]->getId();

                        $recupEnv['nomexp']=$envois[$i]->getExpediteur()->getUsager()->getNom();

                        $recupEnv['paysexp']=$envois[$i]->getExpediteur()->getUsager()->getPays()->getName();
                        $recupEnv['nomdest']=$envois[$i]->getDestinataire()->getUsager()->getNom();
                        $recupEnv['ville']=$envois[$i]->getDestinataire()->getUsager()->getVille();
                        $recupEnv['paysdest']=$envois[$i]->getDestinataire()->getUsager()->getPays()->getName();
                        $recupEnv['heure']=$envois[$i]->getHeure();

                        $recupEnv['poids']=$envois[$i]->getPoids();
                        $recupEnv['taxe']=$envois[$i]->getTarif();
                        $recupEnv['tva']=$envois[$i]->getTva();
                        $k= $k+1;
                        $tabEnvois[$k]=$recupEnv;


                        $recupEnv['nature']='RP';
                        $recupEnv['code']='';
                        $recupEnv['id']='';
                        $recupEnv['nomexp']='';
                        $recupEnv['paysexp']='';
                        $recupEnv['nomdest']='';
                        $recupEnv['ville']='';
                        $recupEnv['paysdest']='';
                        $recupEnv['heure']='';

                        $recupEnv['poids'] = $reppois;
                        $recupEnv['taxe'] = $reptaxe;
                        $recupEnv['tva'] = $reptva;
                        $k= $k+1;
                        $tabEnvois[$k]=$recupEnv;

                    }else{
                        $recupEnv['nature']='ENR';

                        $recupEnv['code']=$envois[$i]->getCodeenvoi();
                        $recupEnv['id']=$envois[$i]->getId();

                       $recupEnv['nomexp']=$envois[$i]->getExpediteur()->getUsager()->getNom();

                        $recupEnv['paysexp']=$envois[$i]->getExpediteur()->getUsager()->getPays()->getName();
                        $recupEnv['nomdest']=$envois[$i]->getDestinataire()->getUsager()->getNom();
                        $recupEnv['ville']=$envois[$i]->getDestinataire()->getUsager()->getVille();
                        
                         if( $envois[$i]->getDestinataire()->getUsager()->getPays()==null){
                              var_dump($recupEnv['code']);die(); 
                         }
                        $recupEnv['paysdest']=$envois[$i]->getDestinataire()->getUsager()->getPays()->getName();
                        $recupEnv['heure']=$envois[$i]->getHeure();

                        $recupEnv['poids']=$envois[$i]->getPoids();
                        $recupEnv['taxe']=$envois[$i]->getTarif();
                        $recupEnv['tva']=$envois[$i]->getTva();
                        $k= $k+1;
                        $tabEnvois[$k]=$recupEnv;
                    }

                }

                //Traitement des derniers envois
               // var_dump($nbenvoiBR);die();
                for($i=$nbinter; $i<=$nbenvoiBR-1; $i++)
                {

                    $reppois= $reppois + $envois[$i]->getPoids();
                    $reptaxe=$reptaxe + $envois[$i]->getTarif();
                    $reptva=$reptva + $envois[$i]->getTva();

                    $recupEnv['nature']='ENR';
                    $recupEnv['code']=$envois[$i]->getCodeenvoi();
                    $recupEnv['id']=$envois[$i]->getId();

                    $recupEnv['nomexp']=$envois[$i]->getExpediteur()->getUsager()->getNom();

                    $recupEnv['paysexp']=$envois[$i]->getExpediteur()->getUsager()->getPays()->getName();
                    $recupEnv['nomdest']=$envois[$i]->getDestinataire()->getUsager()->getNom();
                    $recupEnv['ville']=$envois[$i]->getDestinataire()->getUsager()->getVille();
                    $recupEnv['paysdest']=$envois[$i]->getDestinataire()->getUsager()->getPays()->getName();
                    $recupEnv['heure']=$envois[$i]->getHeure();

                    $recupEnv['poids']=$envois[$i]->getPoids();
                    $recupEnv['taxe']=$envois[$i]->getTarif();
                    $recupEnv['tva']=$envois[$i]->getTva();


                    $tabEnvois[count($tabEnvois)]=$recupEnv;
                }


                if($mod<=9){


                    $i=count($tabEnvois);
                    $recupEnv['nature']='TJ';
                    $recupEnv['nomdest']=$totaljour['nbenvoi'];
                    $recupEnv['poids']=$totaljour['poids'];
                    $recupEnv['taxe']=$totaljour['taxe'];
                    $recupEnv['tva']=$totaljour['tva'];

                    $recupEnv['ville']='';
                    $recupEnv['code']='';
                    $recupEnv['id']='';
                    $recupEnv['nomexp']='';
                    $recupEnv['paysexp']='';
                    $recupEnv['paysdest']='';
                    $recupEnv['heure']='';
                    $tabEnvois[$i]=$recupEnv;


                    $i=count($tabEnvois);
                    $recupEnv['nature']='TA';
                    $recupEnv['nomdest']=$anterieur['nbenvoi'];
                    $recupEnv['poids']=$anterieur['poids'];
                    $recupEnv['taxe']=$anterieur['taxe'];
                    $recupEnv['tva']=$anterieur['tva'];

                    $tabEnvois[$i]=$recupEnv;

                    $i=count($tabEnvois);
                    $recupEnv['nature']='TG';
                    $recupEnv['nomdest']=$totalgen['nbenvoi'];
                    $recupEnv['poids']=$totalgen['poids'];
                    $recupEnv['taxe']=$totalgen['taxe'];
                    $recupEnv['tva']=$totalgen['tva'];

                    $tabEnvois[$i]=$recupEnv;


                }elseif($mod==10){

                    $i=count($tabEnvois);
                    $recupEnv['nature']='VD';
                    $recupEnv['poids']='';
                    $recupEnv['taxe']='';
                    $recupEnv['tva']='';

                    $recupEnv['code']='';
                    $recupEnv['id']='';
                    $recupEnv['nomexp']='';
                    $recupEnv['paysexp']='';
                    $recupEnv['nomdest']='';
                    $recupEnv['ville']='';
                    $recupEnv['paysdest']='';
                    $recupEnv['heure']='';

                    $tabEnvois[$i]=$recupEnv;

                    $i=count($tabEnvois);
                    $recupEnv['nature']='RP';
                    $recupEnv['poids'] = $reppois;
                    $recupEnv['taxe'] = $reptaxe;
                    $recupEnv['tva'] = $reptva;

                    $tabEnvois[$i]=$recupEnv;



                    $i=count($tabEnvois);
                    $recupEnv['nature']='TJ';
                    $recupEnv['nomdest']=$totaljour['nbenvoi'];
                    $recupEnv['poids']=$totaljour['poids'];
                    $recupEnv['taxe']=$totaljour['taxe'];
                    $recupEnv['tva']=$totaljour['tva'];

                    $tabEnvois[$i]=$recupEnv;


                    $i=count($tabEnvois);
                    $recupEnv['nature']='TA';
                    $recupEnv['nomdest']=$anterieur['nbenvoi'];
                    $recupEnv['poids']=$anterieur['poids'];
                    $recupEnv['taxe']=$anterieur['taxe'];
                    $recupEnv['tva']=$anterieur['tva'];

                    $tabEnvois[$i]=$recupEnv;

                    $i=count($tabEnvois);
                    $recupEnv['nature']='TG';
                    $recupEnv['nomdest']=$totalgen['nbenvoi'];
                    $recupEnv['poids']=$totalgen['poids'];
                    $recupEnv['taxe']=$totalgen['taxe'];
                    $recupEnv['tva']=$totalgen['tva'];

                    $tabEnvois[$i]=$recupEnv;
                }






                //var_dump($tabEnvois);die();
            }else{

                for($i=0; $i<$nbenvoiBR; $i++)
                {

                    $reppois= $reppois + $envois[$i]->getPoids();
                    $reptaxe=$reptaxe + $envois[$i]->getTarif();
                    $reptva=$reptva + $envois[$i]->getTva();

                    $recupEnv['nature']='ENR';
                    $recupEnv['poids']=$envois[$i]->getPoids();
                    $recupEnv['taxe']=$envois[$i]->getTarif();
                    $recupEnv['tva']=$envois[$i]->getTva();

                    $recupEnv['code']=$envois[$i]->getCodeenvoi();
                    $recupEnv['id']=$envois[$i]->getId();
                    $recupEnv['nomexp']=$envois[$i]->getExpediteur()->getUsager()->getNom();
                    $recupEnv['paysexp']=$envois[$i]->getExpediteur()->getUsager()->getPays()->getName();
                    $recupEnv['nomdest']=$envois[$i]->getDestinataire()->getUsager()->getNom();
                    $recupEnv['ville']=$envois[$i]->getDestinataire()->getUsager()->getVille();
                    
                     if( $envois[$i]->getDestinataire()->getUsager()->getPays()==null){
                              var_dump($recupEnv['code']);die(); 
                         }
                    
                    $recupEnv['paysdest']=$envois[$i]->getDestinataire()->getUsager()->getPays()->getName();
                    $recupEnv['heure']=$envois[$i]->getHeure();


                    $tabEnvois[$i]=$recupEnv;
                }

                if($nbenvoiBR<=9){

                    $i=count($tabEnvois);
                    $recupEnv['nature']='TJ';
                    $recupEnv['nomdest']=$totaljour['nbenvoi'];
                    //var_dump( $recupEnv['nomdest']);die();
                    $recupEnv['poids']=$totaljour['poids'];
                    $recupEnv['taxe']=$totaljour['taxe'];
                    $recupEnv['tva']=$totaljour['tva'];

                    $recupEnv['code']='';
                    $recupEnv['id']='';
                    $recupEnv['nomexp']='';
                    $recupEnv['paysexp']='';
                    $recupEnv['paysdest']='';
                    $recupEnv['heure']='';

                    $tabEnvois[$i]=$recupEnv;


                    $i=count($tabEnvois);
                    $recupEnv['nature']='TA';
                    $recupEnv['nomdest']=$anterieur['nbenvoi'];
                    $recupEnv['poids']=$anterieur['poids'];
                    $recupEnv['taxe']=$anterieur['taxe'];
                    $recupEnv['tva']=$anterieur['tva'];

                    $tabEnvois[$i]=$recupEnv;

                    $i=count($tabEnvois);
                    $recupEnv['nature']='TG';
                    $recupEnv['nomdest']=$totalgen['nbenvoi'];
                    $recupEnv['poids']=$totalgen['poids'];
                    $recupEnv['taxe']=$totalgen['taxe'];
                    $recupEnv['tva']=$totalgen['tva'];

                    $tabEnvois[$i]=$recupEnv;


                }elseif($nbenvoiBR==10){

                    $i=count($tabEnvois);
                    $recupEnv['nature']='VD';
                    $recupEnv['poids']='';
                    $recupEnv['taxe']='';
                    $recupEnv['tva']='';


                    $tabEnvois[$i]=$recupEnv;

                    $i=count($tabEnvois);
                    $recupEnv['nature']='RP';
                    $recupEnv['poids'] = $reppois;
                    $recupEnv['taxe'] = $reptaxe;
                    $recupEnv['tva'] = $reptva;

                    $tabEnvois[$i]=$recupEnv;


                    $i=count($tabEnvois);
                    $recupEnv['nature']='TJ';
                    $recupEnv['nomdest']=$totaljour['nbenvoi'];
                    $recupEnv['poids']=$totaljour['poids'];
                    $recupEnv['taxe']=$totaljour['taxe'];
                    $recupEnv['tva']=$totaljour['tva'];

                    $tabEnvois[$i]=$recupEnv;


                    $i=count($tabEnvois);
                    $recupEnv['nature']='TA';
                    $recupEnv['nomdest']=$anterieur['nbenvoi'];
                    $recupEnv['poids']=$anterieur['poids'];
                    $recupEnv['taxe']=$anterieur['taxe'];
                    $recupEnv['tva']=$anterieur['tva'];

                    $tabEnvois[$i]=$recupEnv;

                    $i=count($tabEnvois);
                    $recupEnv['nature']='TG';
                    $recupEnv['nomdest']=$totalgen['nbenvoi'];
                    $recupEnv['poids']=$totalgen['poids'];
                    $recupEnv['taxe']=$totalgen['taxe'];
                    $recupEnv['tva']=$totalgen['tva'];

                    $tabEnvois[$i]=$recupEnv;
                }


            }

        }

        return $this->render('envoi/index.html.twig', array(
            'envois' => $envois,
            'bordereau' => $bordereau,
            'totaljour' => $totaljour,
            'anterieur' => $anterieur,
            'totalgen' => $totalgen,
            'tabEnvois' => $tabEnvois,

        ));
    }

    /**
     * Creates a new envoi entity.
     *
     * @Route("/new", name="envoi_new")
     * @Method({"GET", "POST"})
     */
    public function newAction(Request $request)
    {
        if($this->get('security.authorization_checker')->isGranted('ROLE_AGENTGUICHET')){



            $envoi = new Envoi();
            $form = $this->createForm('AppBundle\Form\EnvoiType', $envoi);
            $form->handleRequest($request);
            $em = $this->getDoctrine()->getManager();
            $heuredep=$request->get('heure');
            $date=$request->get('date');
            $mois='%'.substr($date, 2);
            $moisvac='%'.substr($date, 6);

            //var_dump($mdate);die();




            $session=new Session();
            $cpostal = $request->get('cpostal');
            $cpostalexp = $request->get('cpostalexp');
            $evalaur = $request->get('evalaur');


            $utilisateur=$this->getUser();

            $vacations_jour = $em->getRepository('AppBundle:Vacation')->findByIdUserDate($utilisateur, $date);

            if(!$vacations_jour){
                // set and get session attributes
                $session->set('msg', "Attention: La creation d'une vacation est obligatoire");
                return $this->redirectToRoute('vacation_index', array('msg' => '1'));
            }


            if ($form->isSubmitted() && $form->isValid()) {

                if($cpostal!==null){
                    $envoi->getDestinataire()->getUsager()->setCodepostal($cpostal);
                }
                if($cpostalexp!==null){
                    $envoi->getExpediteur()->getUsager()->setCodepostal($cpostalexp);
                }

                if($evalaur!==null){
                    $envoi->setValeur($evalaur);
                }





                $echelle =  $session->get('echelle');
                $client =  $session->get('client');
                $envois = $em->getRepository('AppBundle:Envoi')->findByMoisAgenceEchelleClient($this->getUser()->getAgence()->getId(), $moisvac, $client, $echelle);
               //var_dump($envois);die();

                $typeenvoi='clients occasionnels '.strtolower($echelle);
               // $date=date('d/m/Y');
                //$mois='%'.substr($date, 3);


                //var_dump($mois);die();
                //Recuperation ou creation du type de bordereau d'envoi
                $bordereau = $em->getRepository('AppBundle:Bordereau')->findBordTpypeEnvVacationAgentJour($typeenvoi, $vacations_jour[0]);


                //$vacations = $em->getRepository('AppBundle:Bordereau')->findByMoisAgence($mois, $this->getUser()->getAgence()->getId());
                $vacations = $em->getRepository('AppBundle:Bordereau')->findByMoisAgenceType($mois, $this->getUser()->getAgence()->getId(), $typeenvoi);
                $numvacation=count($vacations)+1;
                if(strlen($numvacation)==1){
                    $numvacation= '00'.$numvacation;
                }elseif(strlen($numvacation)==2){
                    $numvacation= '0'.$numvacation;
                }elseif(strlen($numvacation)==3){
                    $numvacation= $numvacation;
                }
                $bordobject = new Bordereau();
                if(!$bordereau){
                    $bordobject->setTypeenvoi($typeenvoi);
                    $bordobject->setVacation( $vacations_jour[0]);
                    $bordobject->setNumbdr($numvacation);

                    $em = $this->getDoctrine()->getManager();
                    $em->persist($bordobject);
                    $em->flush();
                    //var_dump($bordobject);die();
                }else{
                    $bordobject=$bordereau[0];
                    //var_dump($bordobject);die();
                }




                $envoi->setBordereau($bordobject);
                $envoi->setCodebarre('xxxxx');
                //Conversion Franc cfa en Euro
                $valeur=$envoi->getValeur();

                if($valeur==''){
                    $envoi->setValeur($valeur);
                }else{
                    $valeur=$valeur/656.957;
                    $tabvaleur=explode('.' , $valeur);

                    if(count($tabvaleur)>1){
                        $val2=$tabvaleur[1];
                        if(strlen($val2)>3){

                            $val2=substr($val2, 0, 3);
                            $c3=substr($tabvaleur[1], 3, 1);
                            $val2='0.'. $val2;

                            if($c3>5){
                                $val2=$val2+'0.001';
                            }

                        }else{
                            $val2='0.'. $tabvaleur[1];
                        }
                        $valeur= $tabvaleur[0] + $val2;
                    }
                    $envoi->setValeur($valeur);
                }


                $envoi->setAgence($this->getUser()->getAgence());
                $poids = $request->get('poids');
                $envoi->setPoids($poids);

                $type = $request->get('type');
                $envoi->setType($type);


                $envoi->setClient($client);


                $envoi->setEchelle($echelle);

                $tarif = $request->get('tarif');
                $envoi->setTarif($tarif);

                $tva = $request->get('tva');
                $envoi->setTva($tva);

                $ttc = $request->get('ttc');
                $envoi->setTtc($ttc);

                date_default_timezone_set('UTC');


                $envoi->setDate($date);


                $envoi->setHeure($heuredep);

                $envoi->setEtat('valide');

                //Codification envoi
                if($envoi->getCodeenvoi()=='0'){
                    $ag=$this->codeAgence($this->getUser()->getAgence(),'Occasionnel');
                    $ma=substr($date, 9);
                    if(count($envois)>0){
                        $nd=count($envois)+1;
                        //var_dump($envois[0]['code']);die();
                        $nd=$envois[0]['code']+1;
                    }else{
                        $nd=count($envois)+1;
                    }
                    $jj=substr($date, 0, 2);
                    $mm=substr($date, 3, 2);
                    if(strlen($nd)==1){
                        $nd= '000'.$nd;
                    }elseif(strlen($nd)==2){
                        $nd= '00'.$nd;
                    }elseif(strlen($nd)==3){
                        $nd= '0'.$nd;
                    }
                    $code=$ag.$ma.$nd.$jj.$mm.'SN';

                }else{
                    $code= $envoi->getCodeenvoi();
                }
                $envoi->setCodeenvoi($code);




                $paysexp = $request->get('paysexp');
                $paysexp = $em->getRepository('AppBundle:Pays')->findOneById($paysexp);
                $envoi->getExpediteur()->getUsager()->setPays($paysexp[0]);

                if($valeur==''){
                    $envoi->setValeur($valeur);
                }


                $paysdes = $request->get('paysdes');
                $paysdes = $em->getRepository('AppBundle:Pays')->findOneById($paysdes);
                $envoi->getDestinataire()->getUsager()->setPays($paysdes[0]);

                $villefac=$envoi->getDestinataire()->getUsager()->getVille();
                $envoi->setDesfacture($villefac);

                // var_dump($envoi);die;
                $em = $this->getDoctrine()->getManager();
                $em->persist($envoi);
                $em->flush();

                return $this->redirectToRoute('envoi_show', array('id' => $envoi->getId()));
            }


            $client_bareme='';
            $envoi = $request->get('typeenvoi');
            $code_inter = $request->get('code_inter');




            $document = '';
            $client='';
            $domaine ='';
            $echelle ='';
            $pays =$request->get('pays');
            $nom_paysexp =  '';
            $id_paysexp = '';
            $nom_paysdes =  '';
            $id_paysdes = '';
            $poids = $request->get('poids');



            $session=new Session();
            $depot = $session->get('depot');
            $msg = $request->get('msg');
            if(!$msg){
                // set and get session attributes
                $session->set('paysdes', $pays);
                $session->set('poids', $poids);
                $session->set('envoi', $envoi);
                $session->set('msg', '');
            }else{
                $pays=$session->get('paysdes');
                $poids=$session->get('poids');
                $session->set('envoi', $envoi);
                $session->set('codeinter', $code_inter);
            }

            //Controle de saisie de pays et poids

            if($pays==0){

                // set and get session attributes
                $session->set('msg', 'Attention : La destination est obligatoire !');
                return $this->redirectToRoute('agent_determiner_taxe', array( 'depot' => $depot,'msg' => '1', 'date' =>$date));

            }elseif($envoi=='0'){

                $session=new Session();
                // set and get session attributes
                $session->set('msg', "Attention : Le type d'envoi est  obligatoire");

                return $this->redirectToRoute('agent_determiner_taxe', array( 'depot' => $depot,'msg' => '1', 'date' =>$date));
            }
            elseif(!is_numeric($poids) ){

                $session=new Session();
                // set and get session attributes
                $session->set('codeinter', $code_inter);
                $session->set('msg', 'Attention : Le poids est un nombre réel ex: 0.00 !');
                $session->set('poids', '');

                return $this->redirectToRoute('agent_determiner_taxe', array( 'depot' => $depot,'msg' => '1', 'date' =>$date));
            }

            elseif($poids <=0 || $poids > 30){

                $session=new Session();
                 $session->set('codeinter', $code_inter);
                // set and get session attributes
                $session->set('msg', 'Attention : Le poids est compris entre 0+ et 30 kgs !');
                //$session->set('poids', '');
                return $this->redirectToRoute('agent_determiner_taxe', array( 'depot' => $depot,'msg' => '1', 'date' =>$date));

            }

            $libtaxe = 'rien';
            if($depot=='oen'){
                $client = 'Occasionnel';
                $document = 'Tout type';
                $client_bareme='Tout type';
                $echelle='National';
                $libtaxe = 'NOUVEAU CLIENT';

                //Recuperation du pays destinataire
                $sen=$em->getRepository('AppBundle:Pays')->findOneById($pays);
                $domaine = $sen[0]->getZone()->getId();
                $id_paysdes = $pays;
                $nom_paysdes =  $sen[0]->getName();

                //Recuperation du pays expediteur
                $zone = $em->getRepository('AppBundle:Zone')->findOneByName('Interne');
                $id_zone=$zone[0]->getId();
                $sen=$em->getRepository('AppBundle:Pays')->findByZone($id_zone);
                $id_paysexp = $sen[0]->getId();
                $nom_paysexp =  $sen[0]->getName();

            }elseif($depot=='oei'){

                if(strlen($code_inter)<11 or strlen($code_inter)>13){

                    $session=new Session();
                    // set and get session attributes
                    $session->set('msg', "Le numéro de l'envoi est composé de 13 caractères. Ex: EE123456789SN");
                    $session->set('codeinter', $code_inter);
                    //$session->set('poids', '');
                    return $this->redirectToRoute('agent_determiner_taxe', array( 'depot' => $depot,'msg' => '1', 'date' =>$date));

                } elseif(!$this-> isValidCode($code_inter)){

                    $session=new Session();
                    // set and get session attributes
                    $session->set('msg', 'Ce numéro est déja utilisé');
                     $session->set('codeinter', $code_inter);
                    //$session->set('poids', '');
                    return $this->redirectToRoute('agent_determiner_taxe', array( 'depot' => $depot,'msg' => '1', 'date' =>$date));

                }

                $client = 'Occasionnel';
                $document = $envoi;
                $client_bareme='Occasionnel';
                $echelle='International';

                //Recuperation du pays destinataire
                $sen=$em->getRepository('AppBundle:Pays')->findOneById($pays);
                $domaine = $sen[0]->getZone()->getId();
                $id_paysdes = $pays;
                $nom_paysdes =  $sen[0]->getName();

                //Recuperation du pays expediteur
                $zone = $em->getRepository('AppBundle:Zone')->findOneByName('Interne');
                $id_zone=$zone[0]->getId();
                $sen=$em->getRepository('AppBundle:Pays')->findByZone($id_zone);
                $id_paysexp = $sen[0]->getId();
                $nom_paysexp =  $sen[0]->getName();
            }


            //$client.'|'.$document.'|'.$poids.'|'. $domaine;
            //Ici pour total senegal
            $bareme1 = $em->getRepository('AppBundle:Bareme')->findByPoidsDocClientDomaine($document, $client_bareme, $domaine, $poids, $libtaxe);

            $bareme=array();
            $bareme['client']=$client;
            $bareme['poids']=$poids;
            $bareme['tarif']=$bareme1[0]->getTarif();
            $bareme['tva']=$bareme1[0]->getTva();
            $bareme['ttc']=$bareme1[0]->getTtc();
            $bareme['type_envoi']=$envoi;
            $bareme['id_paysdes']=$id_paysdes;
            $bareme['nom_paysdes']=$nom_paysdes;
            $bareme['id_paysexp']=$id_paysexp;
            $bareme['nom_paysexp']=$nom_paysexp;


            $session->set('tarif', $bareme1[0]->getTarif());
            $session->set('tva', $bareme1[0]->getTva());
            $session->set('ttc', $bareme1[0]->getTtc());

            $session->set('type', $envoi);
            $session->set('echelle', $echelle);
            $session->set('client', $client);
            $session->set('paysexp', $id_paysexp);

            return $this->render('envoi/new.html.twig', array(
                'envoi' => $envoi,
                'form' => $form->createView(),
                'bareme' => $bareme,
                'depot' => $depot,
                'echelle' => $echelle,
                'journnee' => $date,
                'heure' => $heuredep,
                'code_inter' => $code_inter
            ));


        }else{
            return $this->redirectToRoute('homepage');
        }

    }

    /**
     * Finds and displays a envoi entity.
     *
     * @Route("/{id}", name="envoi_show")
     * @Method("GET")
     */
    public function showAction(Envoi $envoi)
    {
        $deleteForm = $this->createDeleteForm($envoi);
        $abonne=$this->isAbonne($envoi);


        return $this->render('envoi/show.html.twig', array(
            'envoi' => $envoi,
            'abonne' => $abonne,
            'delete_form' => $deleteForm->createView(),
        ));
    }



    /**
     * Finds and displays a envoi entity.
     *
     * @Route("/chercher", name="envoi_chercher")
     * @Method({"GET", "POST"})
     */
    public function chercherAction(Request $request)
    {
        $code=strtoupper($request->get('codeenvoi'));

 //var_dump($code);die();
        $em = $this->getDoctrine()->getManager();
        $id_envoyeur="";
        $tabenvoi = $em->getRepository('AppBundle:Envoi') -> findOneByCode($code);
        $envoi="";
        $si_agent="non";

        $id_visiteur=$this->getUser()->getId();
        //var_dump($envoi);die();

        if($tabenvoi){
            $envoi=$tabenvoi[0];
            $id_envoyeur=$envoi->getBordereau()->getVacation()->getUtilisateur()->getId();
        }

        if($id_visiteur==$id_envoyeur){
            $si_agent="oui";
        }

        //var_dump($id_visiteur);die();




        if($envoi==null){
            $msg="Ce code d'envoi n'existe pas ...!";
            return $this->render('envoi/chercher.html.twig', array(
                'msg' => $msg,
            ));
        }else{

            $deleteForm = $this->createDeleteForm($envoi);
            $abonne=$this->isAbonne($envoi);

            return $this->render('envoi/show.html.twig', array(
                'envoi' => $envoi,
                'abonne' => $abonne,
                'si_agent' => $si_agent,
                'delete_form' => $deleteForm->createView(),
            ));
        }


    }


    /**
     * Displays a form to edit an existing envoi entity.
     *
     * @Route("/{id}/edit", name="envoi_edit")
     * @Method({"GET", "POST"})
     */
    public function editAction(Request $request, Envoi $envoi)
    {
        $deleteForm = $this->createDeleteForm($envoi);
        $editForm = $this->createForm('AppBundle\Form\EnvoiType', $envoi);
        $editForm->handleRequest($request);

        //var_dump($envoi);die();
       // var_dump($envoi->getValeur());die();

        $evalaur = $request->get('evalaur');
        //var_dump($evalaur);die();
        $error = $request->get('error');

        if ($editForm->isSubmitted() && $editForm->isValid()) {


            if($evalaur!=null){

                $evalaur=$evalaur/656.957;
                $tabvaleur=explode('.' , $evalaur);

                if(count($tabvaleur)>1){
                    $val2=$tabvaleur[1];
                    if(strlen($val2)>3){

                        $val2=substr($val2, 0, 3);
                        $c3=substr($tabvaleur[1], 3, 1);
                        $val2='0.'. $val2;

                        if($c3>5){
                            $val2=$val2+'0.001';
                        }

                    }else{
                        $val2='0.'. $tabvaleur[1];
                    }
                    $evalaur= $tabvaleur[0] + $val2;
                }
                $envoi->setValeur($evalaur);
           }
           else{
//
//                $evalaur=$envoi->getValeur();
//               // var_dump($evalaur);die();
//                $evalaur=$evalaur/656.957;
//                $tabvaleur=explode('.' , $evalaur);
//
//                if(count($tabvaleur)>1){
//                    $val2=$tabvaleur[1];
//                    if(strlen($val2)>3){
//
//                        $val2=substr($val2, 0, 3);
//                        $c3=substr($tabvaleur[1], 3, 1);
//                        $val2='0.'. $val2;
//
//                        if($c3>5){
//                            $val2=$val2+'0.001';
//                        }
//
//                    }else{
//                        $val2='0.'. $tabvaleur[1];
//                    }
//                    $evalaur= $tabvaleur[0] + $val2;
//                }
//                $envoi->setValeur($evalaur);


            }
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('envoi_show', array('id' => $envoi->getId()));
        }

        return $this->render('envoi/edit.html.twig', array(
            'envoi' => $envoi,
            'edit_form' => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
            'error' =>$error
        ));
    }



    /**
     * Displays a form to edit an existing envoi entity.
     *
     * @Route("/modification/taxe/{id}", name="agent_modification_taxe")
     * @Method({"GET", "POST"})
     */
    public function agentmodificationtaxeAction(Request $request, Envoi $envoi)
    {


        $pays = $request->get('pays');
        $typeenvoi=$request->get('typeenvoi');
        $poids =$request->get('poids');
        $error="0";

        //Recuperation du pays destinataire
        $em = $this->getDoctrine()->getManager();
        $sen=$em->getRepository('AppBundle:Pays')->findOneById($pays);
        $domaine = $sen[0]->getZone()->getId();
        $id_paysdes = $pays;
        $nom_paysdes =  $sen[0]->getName();


var_dump($domaine);die();

        if ($request->getMethod()=='POST') {

            if(!is_numeric($poids) ){
                $error="1";
            }elseif($poids <=0 || $poids > 30){
                $error="2";
            }

        }

        return $this->redirectToRoute('envoi_edit', array('id' => $envoi->getId(),
            'error' =>$error));
    }

    /**
     * Deletes a envoi entity.
     *
     * @Route("/{id}/delete", name="envoi_delete")
     */
    public function deleteAction(Envoi $envoi)
    {
        //var_dump($envoi);die();
        $id_bord=$envoi->getBordereau()->getId();

        $envoi->setEtat('suppr');

        $em = $this->getDoctrine()->getManager();
        $em->persist($envoi);
        $em->flush();

        // Décrémentation des indices supérieurs

        return $this->redirectToRoute('envoi_index', array('id' => $id_bord));
    }

    /**
     * Deletes a envoi entity.
     *
     * @Route("/destfa/modif", name="envoi_destfact")
     */
    public function destfacturerAction()
    {

        $em = $this->getDoctrine()->getManager();


        $tarifs = $em->getRepository('AppBundle:Envoi') -> findAll(1);

        //Suppression des tarifs s'il y en a
        if( count($tarifs) > 0)
        {
            foreach($tarifs as $key => $value)
            {
                $ville=$tarifs[$key]->getDestinataire()->getUsager()->getVille();
                $tarifs[$key]->setDesfacture($ville);
                //var_dump($tarifs[$key]->getDestinataire()->getUsager()->getPays());die();
                $em->persist($tarifs[$key]);
                $em->flush();
            }
        }



        $em->flush();

        // Décrémentation des indices supérieurs

        return $this->redirectToRoute('homepage');
    }


    /**
     * Deletes a envoi entity.
     *
     * @Route("/facture/modification/{id}", name="modification_facture")
     */
    public function modificationfactureAction(Envoi $envoi, Request $request)
    {
        $mois=$request->get('date');
        $version=$request->get('version');
        $abonne = $request->get('abonne');
        $destfacture=$request->get('destfacture');
        $envoi->setDesfacture($destfacture);
        $em = $this->getDoctrine()->getManager();
        $em->persist($envoi);
        $em->flush();
        $em->flush();

        // Décrémentation des indices supérieurs

        return $this->redirectToRoute('abonne_facture',array('abonne'=>$abonne,
            'date'=>$mois, 'version'=>$version, ));
    }


    /**
     * Creates a form to delete a envoi entity.
     *
     * @param Envoi $envoi The envoi entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm(Envoi $envoi)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('envoi_delete', array('id' => $envoi->getId())))
            ->setMethod('DELETE')
            ->getForm()
        ;
    }


    /**
     * @param $agence
     *
     * @return string
     */
    public function codeAgence($agence, $client){
        $em = $this->getDoctrine()->getManager();
        $occ_agence = $em->getRepository('AppBundle:Agence')->findOneById($agence);



        if($occ_agence){
            if($client=='Abonné'){
                return $occ_agence[0]->getcodeR();
            }else{
                return $occ_agence[0]->getcodeO();
            }
        }else{
            return "";
        }

    }


    /**
     * @param $username
     *
     * @return boolean
     */
    public function isValidCode($codeenvoi){
        $em = $this->getDoctrine()->getManager();
        $validCode = $em->getRepository('AppBundle:Envoi')->findOneByCodeenvoi($codeenvoi);

        if($validCode){
            return false;
        }else{
            return true;
        }

    }


    /**
     * @param $envoi
     *
     * @return integer
     */
    public function isAbonne($envoi){
        $em = $this->getDoctrine()->getManager();
        $validCode = $em->getRepository('AppBundle:AbonneEnvoi')->findOneByIdEnvoi($envoi);

        if($validCode){
            return $validCode[0]->getId();
        }else{
            return 0;
        }

    }




}
