<?php

namespace AppBundle\Controller;

use AppBundle\Entity\AbonneEnvoi;
use AppBundle\Entity\Bordereau;
use AppBundle\Entity\Facture;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;

/**
 * Abonneenvoi controller.
 *
 * @Route("agent/abonne/envoi")
 */
class AbonneEnvoiController extends Controller
{
    /**
     * Lists all abonneEnvoi entities.
     *
     * @Route("/", name="abonneenvoi_index")
     * @Method("GET")
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();

        $abonneEnvois = $em->getRepository('AppBundle:AbonneEnvoi')->findAll();

        return $this->render('abonneenvoi/index.html.twig', array(
            'abonneEnvois' => $abonneEnvois,
        ));
    }

    /**
     * Creates a new abonneEnvoi entity.
     *
     * @Route("/new", name="abonneenvoi_new")
     * @Method({"GET", "POST"})
     */
    public function newAction(Request $request)
    {
        if($this->get('security.authorization_checker')->isGranted('ROLE_AGENTGUICHET')){

            $abonneEnvoi = new Abonneenvoi();
            $form = $this->createForm('AppBundle\Form\AbonneEnvoiType', $abonneEnvoi);
            $form->handleRequest($request);
            $em = $this->getDoctrine()->getManager();

            $heuredep=$request->get('heure');
            $date=$request->get('date');
            $mois='%'.substr($date, 2);
            $moisvac='%'.substr($date, 6);
            
            $abonnes=$em->getRepository('AppBundle:Abonne')->findByAbonnes();
            $listepays=array();

            $session=new Session();
            $cpostal = $request->get('cpostal');
            $evalaur = $request->get('evalaur');

            //var_dump($cpostal);die();

            $utilisateur=$this->getUser();
            //Verification de la vacation du jour
            $vacations_jour = $em->getRepository('AppBundle:Vacation')->findByIdUserDate($utilisateur, $date);
            if(!$vacations_jour){
                // set and get session attributes
                $session->set('msg', "Créer d'abord votre vacation");
                return $this->redirectToRoute('vacation_index', array('msg' => '1'));
            }

            if ($form->isSubmitted() && $form->isValid()) {



                if($cpostal!==null){
                    $abonneEnvoi->getEnvoi()->getDestinataire()->getUsager()->setCodepostal($cpostal);
                }
                if($evalaur!==null){
                    $abonneEnvoi->getEnvoi()->setValeur($evalaur);
                }




                $echelle =  $session->get('echelle');
                $client =  $session->get('client');
                $envois = $em->getRepository('AppBundle:Envoi')->findByMoisAgenceEchelleClient($this->getUser()->getAgence()->getId(), $moisvac, $client, $echelle);
                $tabenvois = $em->getRepository('AppBundle:Envoi')->findByNEnvois($this->getUser()->getAgence()->getId(), $moisvac, $client, $echelle);
                $nbreenvois=count($tabenvois);

                $typeenvoi='clients réguliers '.strtolower($echelle);


                //Recuperation ou creation du type de bordereau d'envoi
                $bordereau = $em->getRepository('AppBundle:Bordereau')->findBordTpypeEnvVacationAgentJour($typeenvoi, $vacations_jour[0]);


                $vacations = $em->getRepository('AppBundle:Bordereau')->findByMoisAgenceType($mois, $this->getUser()->getAgence()->getId(),$typeenvoi);
                $numvacation=count($vacations)+1;
                if(strlen($numvacation)==1){
                    $numvacation= '00'.$numvacation;
                }elseif(strlen($numvacation)==2){
                    $numvacation= '0'.$numvacation;
                }elseif(strlen($numvacation)==3){
                    $numvacation= $numvacation;
                }

                //var_dump($vacations);die();

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
                }

                //var_dump('yes');die();


                $abonneEnvoi->getEnvoi()->setBordereau($bordobject);
                $id_abonne =  $session->get('abonne');
                //var_dump($id_abonne);die();
                $id_abonnetab = $em->getRepository('AppBundle:Abonne')->findOneById($id_abonne);

                $abonneEnvoi->setAbonne($id_abonnetab[0]);


                $abonneEnvoi->getEnvoi()->setCodebarre('xxxxx');

                //Conversion Franc cfa en Euro
                $valeur=$abonneEnvoi->getEnvoi()->getValeur();
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
                $abonneEnvoi->getEnvoi()->setValeur($valeur);

                //achangerr
                $poids =  $session->get('poids');
               // var_dump($poids);die();
                $abonneEnvoi->getEnvoi()->setPoids($poids);

                $abonneEnvoi->getEnvoi()->setAgence($this->getUser()->getAgence());

                $type =  $session->get('type');
                $abonneEnvoi->getEnvoi()->setType($type);


                $abonneEnvoi->getEnvoi()->setClient($client);


                $abonneEnvoi->getEnvoi()->setEchelle($echelle);

                //achanger
                $tarif =  $session->get('tarif');
                $abonneEnvoi->getEnvoi()->setTarif($tarif);

                //achanger
                $tva =  $session->get('tva');
                $abonneEnvoi->getEnvoi()->setTva($tva);

                //achanger
                $ttc =  $session->get('ttc');
                $abonneEnvoi->getEnvoi()->setTtc($ttc);

                date_default_timezone_set('UTC');


                $abonneEnvoi->getEnvoi()->setDate($date);


                $abonneEnvoi->getEnvoi()->setHeure($heuredep);

                $abonneEnvoi->getEnvoi()->setEtat('valide');

                //Codification envoi
                if($abonneEnvoi->getEnvoi()->getCodeenvoi()=='0'){
                    $ag=$this->codeAgence($this->getUser()->getAgence(),'Abonné');
                    $ma=substr($date, 9);

                    if(count($envois)>0){

                        //var_dump($envois[0]['code']);die();
                        $nd=$envois[0]['code']+1;

                        if($envois[0]['code']=="9999"){
                            $nd= $nbreenvois-$envois[0]['code']+1;
                            $idagsup=$this->getUser()->getAgence()->getId();
                            if($idagsup==44){ //44
                                $nd= $nbreenvois+32-9999;
                            }
                            //var_dump($nd);die();
                        }
                        $idagsup=$this->getUser()->getAgence()->getId();
                        if($idagsup==44){ //44
                            
                           $nd= substr($tabenvois[$nbreenvois-1]['codeenvoi'], 3,4)+1;
                              // var_dump($nd);die();    
                        }
                       

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
                    $code= $abonneEnvoi->getEnvoi()->getCodeenvoi();
                }
                $abonneEnvoi->getEnvoi()->setCodeenvoi($code);




                $paysexp =  $session->get('paysexp');
                $paysexp = $em->getRepository('AppBundle:Pays')->findOneById($paysexp);
                $abonneEnvoi->getEnvoi()->getExpediteur()->getUsager()->setPays($paysexp[0]);

//achangerr
                $paysdes =  $session->get('paysdes');
                $paysdes = $em->getRepository('AppBundle:Pays')->findOneById($paysdes);
                $abonneEnvoi->getEnvoi()->getDestinataire()->getUsager()->setPays($paysdes[0]);

                $villefac=$abonneEnvoi->getEnvoi()->getDestinataire()->getUsager()->getVille();
                $abonneEnvoi->getEnvoi()->setDesfacture($villefac);

                //var_dump('ok');die();
                $em = $this->getDoctrine()->getManager();
                $em->persist($abonneEnvoi);
                $em->flush();



                //Enregistrement inforamation de la facture du mois du client
                $periode=substr($date, 3);
                $annee='%'.substr($date, 6);

                $facture = $em->getRepository('AppBundle:Facture')->findOneByIdAbonnePeriode($id_abonne, $periode);
                $ordfecture = $em->getRepository('AppBundle:Facture')->findOneByIdAbonnePeriode($id_abonne, $periode);
                $numfact=count($ordfecture)+1;
                if(strlen($numfact)==1){
                    $numfact= '00'.$numfact;
                }elseif(strlen($numfact)==2){
                    $numfact= '0'.$numfact;
                }elseif(strlen($numfact)==3){
                    $numfact= $numfact;
                }


                if(!$facture){

                    //var_dump($id_abonne);die();
                    $facture=new Facture();
                    $facture->setAbonne($id_abonnetab[0]);
                    $facture->setPeriode($periode);
                    $facture->setNumfacture('---');
                    $facture->setNumfacture($numfact);
                    $facture->setEtat('NON');
                    $virement='-POSTEFINANCES N°001052800101, code Bank: 25107, code guichet: 01240, clé RIB: 40';
                    $facture->setVirement($virement);
                    //var_dump($facture);die();
                    $em->persist($facture);
                    $em->flush();



                    $facturesab = $em->getRepository('AppBundle:Facture')->findByPeriodeFacturesAbonnes($periode);
                    $num=0;
                    if( count($facturesab) > 0)
                    {
                        foreach($facturesab as $key => $value)
                        {
                            $id_ab=$facturesab[$key]->getAbonne()->getId();
                            $percent='%/'.$periode;
                            $enab=$em->getRepository('AppBundle:AbonneEnvoi')->findByIdAbonneMois($id_ab, $percent);

                            // var_dump();die();
                            if($enab)
                            {
                                $num=$num+1;
                                $numfact=$num;
                                if(strlen($numfact)==1){
                                    $numfact= '00'.$numfact;
                                }elseif(strlen($numfact)==2){
                                    $numfact= '0'.$numfact;
                                }elseif(strlen($numfact)==3){
                                    $numfact= $numfact;
                                }
                                $facturesab[$key]->setNumfacture($numfact);
                                $em->persist($facturesab[$key]);
                                $em->flush();
                            }
                        }
                    }



                }

                //var_dump($facture);die();


                return $this->redirectToRoute('envoi_show', array('id' => $abonneEnvoi->getEnvoi()->getId()));
            }


            $type_envoi = $request->get('typeenvoi');
            $code_inter = $request->get('code_inter');

            $document = '';
            $domaine ='';
            $echelle ='';
            $client='';

            $pays =$request->get('pays');
            $envoi = $request->get('typeenvoi');
            $code_abonne = $request->get('code_abonne');
            $nom_paysexp =  '';
            $id_paysexp = '';
            $nom_paysdes =  '';
            $id_paysdes = '';
            $poids = $request->get('poids');
            $depot =  $session->get('depot');

//var_dump($code_abonne);die();
            //Recuperation des propiétes de l'exp


            $abonnetab = $em->getRepository('AppBundle:Abonne')->findOneByNom($code_abonne);
            //var_dump($abonnetab);die();

            //var_dump($abonnetab);die();
            //Controle de saisie de pays et poids
            //Controle de saisie de pays et poids

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
                $id_pays=$session->get('paysdes');
                $poids=$session->get('poids');
                $envoi=$session->get('envoi');
            }


//            if($pays==0){
//
//                $session=new Session();
//                // set and get session attributes
//                $session->set('msg', 'Attention : La destination est obligatoire !');
//
//                return $this->redirectToRoute('agent_determiner_taxe', array( 'depot' => $depot,'msg' => '1'));
//            }
//            elseif($envoi=='0'){
//
//                $session=new Session();
//                // set and get session attributes
//                $session->set('msg', 'Attention : Le type d envoi est  obligatoire');
//
//                return $this->redirectToRoute('agent_determiner_taxe', array( 'depot' => $depot,'msg' => '1'));
//            }
//            elseif(!is_numeric($poids) ){
//
//                $session=new Session();
//                // set and get session attributes
//                $session->set('msg', 'Attention : Le poids est un nombre réel ex: 0.00 !');
//                $session->set('poids', '');
//
//                return $this->redirectToRoute('agent_determiner_taxe', array( 'depot' => $depot,'msg' => '1'));
//            }
//
//            elseif($poids <=0 || $poids > 30){
//
//                $session=new Session();
//                // set and get session attributes
//                $session->set('msg', 'Attention : Le poids est compris entre 0+ et 30 kgs !');
//                $session->set('poids', '');
//
//                return $this->redirectToRoute('agent_determiner_taxe', array( 'depot' => $depot,'msg' => '1'));
//            } elseif(!$abonnetab){
//
//                $session->set('msg', 'Attention : Le nom du client est incorrect !');
//                return $this->redirectToRoute('agent_determiner_taxe', array( 'depot' => $depot,'msg' => '1'));
//            }



            $libtaxe = '';
            $nomabonne='';
            if($abonnetab){
                $libtaxe = $abonnetab[0]->getType();
                $nomabonne = $abonnetab[0]->getNom();
            } else {
                $session=new Session();
                // set and get session attributes
                $session->set('nomabonne', $nomabonne);
                $session->set('codeinter', $code_inter);
                $session->set('msg', 'Attention : Le nom du client est incorrect !');
                return $this->redirectToRoute('agent_determiner_taxe', array( 'depot' => $depot,'msg' => '1', 'date' =>$date));
            }

            if($depot=='ren'){
                $client = 'Abonné';
                $document = 'Tout type';
                $client_bareme='Tout type';
                $echelle='National';
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
            }elseif($depot=='rei'){
                $listepays = $em->getRepository('AppBundle:Pays')->findByName();
                if(strlen($code_inter)<11 or strlen($code_inter)>13){

                    $session=new Session();
                    // set and get session attributes
                    $session->set('nomabonne', $nomabonne);
                    $session->set('msg', "Le numéro de l'envoi est composé de 13 caractères. Ex: EE123456789SN");
                    //$session->set('poids', '');
                    return $this->redirectToRoute('agent_determiner_taxe', array( 'depot' => $depot,'msg' => '1', 'date' =>$date));

                } elseif(!$this-> isValidCode($code_inter)){

                    $session=new Session();
                    // set and get session attributes
                    $session->set('msg', 'Ce numéro est déja utilisé');
                    //$session->set('poids', '');
                    return $this->redirectToRoute('agent_determiner_taxe', array( 'depot' => $depot,'msg' => '1'));

                }


                $client = 'Abonné';
                $document = $envoi;
                $client_bareme='Abonne';
                $echelle='International';
                $libtaxe = 'rien';

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
            //var_dump($abonnetab);die();



            if($code_abonne=='AMBASSADE  CANADA' and $nom_paysdes=='Cameroun' and $poids<=1){

                $baremetab = $em->getRepository('AppBundle:extra_bareme')->findByPoidsClient($code_abonne, $poids);


            } else{
                $baremetab = $em->getRepository('AppBundle:Bareme')->findByPoidsDocClientDomaine($document, $client_bareme, $domaine, $poids, $libtaxe);
            }
            //Ici pour total sénégal



            //var_dump($document.'|'.$client_bareme.'|'.$domaine.'|'.$poids.'|'.$libtaxe);die();
            $bareme=array();
            $bareme['client']=$client;
            $bareme['poids']=$poids;
            $bareme['tarif']=$baremetab[0]->getTarif();
            $bareme['tva']=$baremetab[0]->getTva();
            $bareme['ttc']=$baremetab[0]->getTtc();
            $bareme['type_envoi']=$type_envoi;
            $bareme['id_paysdes']=$id_paysdes;
            $bareme['nom_paysdes']=$nom_paysdes;
            $bareme['id_paysexp']=$id_paysexp;
            $bareme['nom_paysexp']=$nom_paysexp;

            $session->set('type', $type_envoi);
            $session->set('echelle', $echelle);
            $session->set('client', $client);
            $session->set('paysexp', $id_paysexp);

            $tarif=$baremetab[0]->getTarif();
            $tva=$baremetab[0]->getTva();
            $ttc=$baremetab[0]->getTtc();

            $session->set('tarif', $tarif);
            $session->set('tva', $tva);
            $session->set('ttc', $ttc);
            $session->set('nomabonne', $nomabonne);
            $session->set('codeinter', $code_inter);

                $abonne=$abonnetab[0];
                 $session->set('abonne', $abonne->getId());
                return $this->render('abonneenvoi/new.html.twig', array(
                    'abonneEnvoi' => $abonneEnvoi,
                    'form' => $form->createView(),
                    'abonne' => $abonne,
                    'bareme' => $bareme,
                    'depot' => $depot,
                    'echelle' => $echelle,
                    'journnee' => $date,
                    'heure' => $heuredep,
                    'code_inter' => $code_inter,
                    'pays' => $listepays,
                    'abonnes'=>$abonnes
                ));


        }else{
            return $this->redirectToRoute('homepage');
        }

    }


    /**
     * Displays a form to edit an existing abonneEnvoi entity.
     *
     * @Route("/{id}/edit", name="abonneenvoi_edit")
     * @Method({"GET", "POST"})
     */
    public function editAction(Request $request, AbonneEnvoi $abonneEnvoi)
    {
        $deleteForm = $this->createDeleteForm($abonneEnvoi);
        $editForm = $this->createForm('AppBundle\Form\AbonneEnvoiType', $abonneEnvoi);
        $editForm->handleRequest($request);

        if ($editForm->isSubmitted() && $editForm->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('abonneenvoi_edit', array('id' => $abonneEnvoi->getId()));
        }

        return $this->render('abonneenvoi/edit.html.twig', array(
            'abonneEnvoi' => $abonneEnvoi,
            'edit_form' => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ));
    }


    /**
     * Deletes a envoi entity.
     *
     * @Route("/{id}", name="abonneenvoi_delete")
     */
    public function deleteAction(AbonneEnvoi $envoi)
    {

        $id_bord=$envoi->getEnvoi()->getBordereau()->getId();
        //$id_envoi=$envoi->getEnvoi()->getId();
        //$echelle=$envoi->getEnvoi()->getEchelle();

        $envoi->getEnvoi()->setEtat('suppr');

        $em = $this->getDoctrine()->getManager();

        $em->persist($envoi);

        $em->flush();
        return $this->redirectToRoute('envoi_index', array('id' => $id_bord));
    }


    /**
     * Creates a form to delete a abonneEnvoi entity.
     *
     * @param AbonneEnvoi $abonneEnvoi The abonneEnvoi entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm(AbonneEnvoi $abonneEnvoi)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('abonneenvoi_delete', array('id' => $abonneEnvoi->getId())))
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

}
