<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Bareme;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Bareme controller.
 *
 * @Route("admin/bareme")
 */
class BaremeController extends Controller
{


    /**
     * Lists all bareme entities national.
     *
     * @Route("/national", name="bareme_national")
     * @Method({"GET", "POST"})
     */
    public function listNationalAction(Request $request)
    {

        //Declaration de entity manager et tableaux de tarifs
        $em = $this->getDoctrine()->getManager();
        $baremes1=array();
        $baremes2=array();
        $typeclient = $request->get('typeclient');
        $libtaxe='';

        if($typeclient=='nc'){
            $libtaxe='NOUVEAU CLIENT';
        }elseif($typeclient=='ac'){
            $libtaxe='ANCIEN CLIENT';
        }elseif($typeclient=='ip'){
            $libtaxe='INSTITUT PASTEUR';
        }elseif($typeclient=='ef'){
            $libtaxe='EDITION 3 FLEUVES';
        }else{
            return $this->redirectToRoute('homepage');
        }

        //var_dump($typeclient);die();

        //Recuperation du domaine et initialisation des tableaux de tarifs
        $zone = $em->getRepository('AppBundle:Zone')->findOneByName('Interne');
        if($zone){
            $id_zone=$zone[0]->getId();
            $baremes1 = $em->getRepository('AppBundle:Bareme')->findByDomaine1($id_zone, $libtaxe);
            $baremes2 = $em->getRepository('AppBundle:Bareme')->findByDomaine2($id_zone, $libtaxe);
        }

        //Recuperation des parametres de tarification
        $doc  = 'Tout type';
        $client ='Tout type';
        $domaine=$id_zone;
        $tarifs = $em->getRepository('AppBundle:Bareme') -> findByDocClientDomaine($doc, $client, $domaine, $libtaxe);
        $tranche_poids='';
        $min_montant='';
        $tranche_montant='';
        $max_poids='';

        if( count($tarifs) > 0)
        {
            $tranche_poids=$tarifs[0]->getPoidsmax();
            $min_montant=$tarifs[0]->getTarif();
            $tranche_montant=$tarifs[1]->getTarif()- $tarifs[0]->getTarif();;
            $max_poids=$tarifs[count($tarifs)-1]->getPoidsmax();
        }


        //Renvoi des tableaux de tarifs et parametres de tarification
        return $this->render('bareme/index.html.twig', array(
            'baremes1' => $baremes1,
            'baremes2' => $baremes2,
            'tranche_poids' => $tranche_poids,
            'min_montant' => $min_montant,
            'tranche_montant' => $tranche_montant,
            'max_poids' => $max_poids,
            'typeclient' => $typeclient,
        ));
    }

    /**
     * Lists all bareme entities.
     *
     * @Route("/abonnes", name="bareme_abonne")
     * @Method({"GET", "POST"})
     */
    public function listAbonneAction(Request $request)
    {

        //Declaration de entity manager et tableaux de tarifs
        $em = $this->getDoctrine()->getManager();
        $zone1bar1=array();
        $zone1bar2=array();
        $zone1bar3=array();
        $baremes4=array();

        //Recuperation du domaine et initialisation des tableaux de tarifs
        $var_zone=$request->get('zone');

        $zone = $em->getRepository('AppBundle:Zone')->findOneByName($var_zone);
        if(!$zone){
            return $this->redirectToRoute('zone_index');
        }
        $client ='Abonne';
        $id_zone=$zone[0]->getId();
        $nom_zone=$zone[0]->getDomaine();
        $zone1bar1 = $em->getRepository('AppBundle:Bareme')->findByDomaineTypedocClient1($id_zone, $client,'Document');
        $zone1bar2 = $em->getRepository('AppBundle:Bareme')->findByDomaineTypedocClient2($id_zone, $client,'Document');
        $zone1bar3 = $em->getRepository('AppBundle:Bareme')->findByDomaineTypedocClient1($id_zone, $client,'Non document');
        $zone1bar4 = $em->getRepository('AppBundle:Bareme')->findByDomaineTypedocClient2($id_zone, $client,'Non document');

        //Recuperation des parametres de tarification

        $domaine=$id_zone;
        $tarifs_doc = $em->getRepository('AppBundle:Bareme') -> findByDocClientDomaine('Document', $client, $domaine, 'rien');
        $tarifs_ndoc = $em->getRepository('AppBundle:Bareme') -> findByDocClientDomaine('Non document', $client, $domaine, 'rien');

        $tranche_poids_doc='';
        $min_montant_doc='';
        $tranche_montant_doc='';
        $max_poids_doc='';

        $tranche_poids_ndoc='';
        $min_montant_ndoc='';
        $tranche_montant_ndoc='';
        $max_poids_ndoc='';

        if( count($tarifs_doc) > 0)
        {
            $tranche_poids_doc=$tarifs_doc[0]->getPoidsmax();
            $min_montant_doc=$tarifs_doc[0]->getTarif();
            $tranche_montant_doc=$tarifs_doc[1]->getTarif()- $tarifs_doc[0]->getTarif();;
            $max_poids_doc=$tarifs_doc[count($tarifs_doc)-1]->getPoidsmax();
        }

        if( count($tarifs_ndoc) > 0)
        {
            $tranche_poids_ndoc=$tarifs_ndoc[0]->getPoidsmax();
            $min_montant_ndoc=$tarifs_ndoc[0]->getTarif();
            $tranche_montant_ndoc=$tarifs_ndoc[1]->getTarif()- $tarifs_ndoc[0]->getTarif();;
            $max_poids_ndoc=$tarifs_ndoc[count($tarifs_ndoc)-1]->getPoidsmax();
        }


        //Renvoi des tableaux de tarifs et parametres de tarification
        return $this->render('bareme/indexab.html.twig', array(
            'zone1bar1' => $zone1bar1,
            'zone1bar2' => $zone1bar2,
            'zone1bar3' => $zone1bar3,
            'zone1bar4' => $zone1bar4,
            'zone' => $var_zone,
            'tranche_poids_doc' => $tranche_poids_doc,
            'min_montant_doc' => $min_montant_doc,
            'tranche_montant_doc' => $tranche_montant_doc,
            'max_poids_doc' => $max_poids_doc,
            'tranche_poids_ndoc' => $tranche_poids_ndoc,
            'min_montant_ndoc' => $min_montant_ndoc,
            'tranche_montant_ndoc' => $tranche_montant_ndoc,
            'max_poids_ndoc' => $max_poids_ndoc,
            'nom_zone' => $nom_zone
        ));
    }


    /**
     * Lists all bareme entities.
     *
     * @Route("/occasionnels", name="bareme_occasionnel")
     * @Method({"GET", "POST"})
     */
    public function listOccasionnelAction(Request $request)
    {
        //Declaration de entity manager et tableaux de tarifs
        $em = $this->getDoctrine()->getManager();
        $zone1bar1=array();
        $zone1bar2=array();
        $zone1bar3=array();
        $baremes4=array();


        //Recuperation du domaine et initialisation des tableaux de tarifs
        $var_zone=$request->get('zone');

        $zone = $em->getRepository('AppBundle:Zone')->findOneByName($var_zone);
        if(!$zone){
            return $this->redirectToRoute('zone_index');
        }
        $client ='Occasionnel';
        $id_zone=$zone[0]->getId();
        $nom_zone=$zone[0]->getDomaine();
        $zone1bar1 = $em->getRepository('AppBundle:Bareme')->findByDomaineTypedocClient1($id_zone, $client,'Document');
        $zone1bar2 = $em->getRepository('AppBundle:Bareme')->findByDomaineTypedocClient2($id_zone, $client,'Document');
        $zone1bar3 = $em->getRepository('AppBundle:Bareme')->findByDomaineTypedocClient1($id_zone, $client,'Non document');
        $zone1bar4 = $em->getRepository('AppBundle:Bareme')->findByDomaineTypedocClient2($id_zone, $client,'Non document');

        //Recuperation des parametres de tarification

        $domaine=$id_zone;
        $tarifs_doc = $em->getRepository('AppBundle:Bareme') -> findByDocClientDomaine('Document', $client, $domaine, 'rien');
        $tarifs_ndoc = $em->getRepository('AppBundle:Bareme') -> findByDocClientDomaine('Non document', $client, $domaine, 'rien');

        $tranche_poids_doc='';
        $min_montant_doc='';
        $tranche_montant_doc='';
        $max_poids_doc='';

        $tranche_poids_ndoc='';
        $min_montant_ndoc='';
        $tranche_montant_ndoc='';
        $max_poids_ndoc='';

        if( count($tarifs_doc) > 0)
        {
            $tranche_poids_doc=$tarifs_doc[0]->getPoidsmax();
            $min_montant_doc=$tarifs_doc[0]->getTarif();
            $tranche_montant_doc=$tarifs_doc[1]->getTarif()- $tarifs_doc[0]->getTarif();;
            $max_poids_doc=$tarifs_doc[count($tarifs_doc)-1]->getPoidsmax();
        }

        if( count($tarifs_ndoc) > 0)
        {
            $tranche_poids_ndoc=$tarifs_ndoc[0]->getPoidsmax();
            $min_montant_ndoc=$tarifs_ndoc[0]->getTarif();
            $tranche_montant_ndoc=$tarifs_ndoc[1]->getTarif()- $tarifs_ndoc[0]->getTarif();;
            $max_poids_ndoc=$tarifs_ndoc[count($tarifs_ndoc)-1]->getPoidsmax();
        }


        //Renvoi des tableaux de tarifs et parametres de tarification
        return $this->render('bareme/indexoc.html.twig', array(
            'zone1bar1' => $zone1bar1,
            'zone1bar2' => $zone1bar2,
            'zone1bar3' => $zone1bar3,
            'zone1bar4' => $zone1bar4,
            'zone' => $var_zone,
            'tranche_poids_doc' => $tranche_poids_doc,
            'min_montant_doc' => $min_montant_doc,
            'tranche_montant_doc' => $tranche_montant_doc,
            'max_poids_doc' => $max_poids_doc,
            'tranche_poids_ndoc' => $tranche_poids_ndoc,
            'min_montant_ndoc' => $min_montant_ndoc,
            'tranche_montant_ndoc' => $tranche_montant_ndoc,
            'max_poids_ndoc' => $max_poids_ndoc,
            'nom_zone' => $nom_zone
        ));
    }




    /**
     * Deletes and create a bareme entity.
     *
     * @Route("/new/baremenational", name="new_bareme_national")
     */
    public function creerBaremeNationalAction(Request $request)
    {
        //Recuperation informations generales de tarifs
        $typeclient = $request->get('typeclient');
        $libtaxe='';

        if($typeclient=='nc'){
            $libtaxe='NOUVEAU CLIENT';
        }elseif($typeclient=='ac'){
            $libtaxe='ANCIEN CLIENT';
        }elseif($typeclient=='ip'){
            $libtaxe='INSTITUT PASTEUR';
        } elseif($typeclient=='ef'){
            $libtaxe='EDITION 3 FLEUVES';
        }
        else{
            return $this->redirectToRoute('homepage');
        }

        $tranche_poids = $request->get('tranche_poids');
        $tranche_montant = $request->get('tranche_montant');
        $max_poids = $request->get('max_poids');
        $min_montant = $request->get('min_montant');
        //Recuperation de id du domaine national
        $em = $this->getDoctrine()->getManager();
        $zone = $em->getRepository('AppBundle:Zone')->findOneByName('Interne');
        $id_zone='';
        if($zone){
            $id_zone=$zone[0]->getId();
        }

        //Recuperation des tarifs
        $doc  = 'Tout type';
        $client ='Tout type';
        $domaine=$id_zone;
        $tarifs = $em->getRepository('AppBundle:Bareme') -> findByDocClientDomaine($doc, $client, $domaine, $libtaxe);

        //Suppression des tarifs s'il y en a
        if( count($tarifs) > 0)
        {
            foreach($tarifs as $key => $value)
            {
                $em->remove($tarifs[$key]);
                $em->flush();
            }
        }

        //Creation des tarifs par tranche de poids et de montant

        while ($tranche_poids <= $max_poids) {

            $bareme = new Bareme();

            $bareme->setTypeclient($libtaxe);
            $bareme->setDomaine($zone[0]);
            $bareme->setDocument($doc);
            $bareme->setClient($client);
            $bareme->setPoidsmax($tranche_poids);
            $poidsmin= $bareme->getPoidsmax()-0.5;
            $bareme->setPoidsmin($poidsmin);
            $bareme->setTarif($min_montant);
            $tva=$bareme->getTarif()*18/100;
            $bareme->setTva($tva);
            $bareme->setTtc($bareme->getTarif()+$tva);

            $em->persist($bareme);
            $em->flush();

            $tranche_poids = $tranche_poids + 0.5;
            $min_montant = $min_montant + $tranche_montant;
        }

       return $this->redirectToRoute('bareme_national',array('typeclient'=>$typeclient));
    }



    /**
     * Deletes and create a bareme entity.
     *
     * @Route("/new/baremeclient", name="new_bareme_client")
     */
    public function creerBaremeClientAction(Request $request)
    {
        //Recuperation informations generales de tarifs
        $tranche_poids = $request->get('tranche_poids');
        $tranche_montant = $request->get('tranche_montant');
        $max_poids = $request->get('max_poids');
        $min_montant = $request->get('min_montant');
        $type_doc = $request->get('type_doc');
        $client = $request->get('client');

        //Recuperation de id du domaine national
        $em = $this->getDoctrine()->getManager();
        $var_zone=$request->get('zone');
        $zone = $em->getRepository('AppBundle:Zone')->findOneByName($var_zone);
        $id_zone='';
        if($zone){
            $id_zone=$zone[0]->getId();
        }


        //Recuperation des tarifs
        $domaine=$id_zone;
        $tarifs = $em->getRepository('AppBundle:Bareme') -> findByDocClientDomaine($type_doc, $client, $domaine, 'rien');

        //Suppression des tarifs s'il y en a
        if( count($tarifs) > 0)
        {
            foreach($tarifs as $key => $value)
            {
                $em->remove($tarifs[$key]);
                $em->flush();
            }
        }

        //Creation des tarifs par tranche de poids et de montant

        while ($tranche_poids <= $max_poids) {

            $bareme = new Bareme();
            $bareme->setTypeclient('rien');
            $bareme->setDomaine($zone[0]);
            $bareme->setDocument($type_doc);
            $bareme->setClient($client);
            $bareme->setPoidsmax($tranche_poids);
            $poidsmin= $bareme->getPoidsmax()-0.5;
            $bareme->setPoidsmin($poidsmin);
            $bareme->setTarif($min_montant);
            $tva=0;
            $bareme->setTva($tva);
            $bareme->setTtc($bareme->getTarif()+$tva);

            $em->persist($bareme);
            $em->flush();

            $tranche_poids = $tranche_poids + 0.5;
            $min_montant = $min_montant + $tranche_montant;
        }


//var_dump($type_doc);die();



        if( $client=='Abonne')
        {
            return $this->redirectToRoute('bareme_abonne', array(
                'zone' => $var_zone,
            ));

        }else{
            return $this->redirectToRoute('bareme_occasionnel', array(
                'zone' => $var_zone,
            ));
        }


    }




    /**
     * Deletes a bareme entity.
     *
     * @Route("delete/bareme", name="bareme_delete")
     *
     */
    public function deleteBaremeAction(Request $request)
    {
        //Recuperation de id du domaine national
        $n_zone = $request->get('n_zone');
        $documt = $request->get('documt');
        $client = $request->get('client');
        $tclient=$request->get('tclient');

        if($tclient=='nc'){
            $libtaxe='NOUVEAU CLIENT';
        }elseif($tclient=='ac'){
            $libtaxe='ANCIEN CLIENT';
        }elseif($tclient=='ip'){
            $libtaxe='INSTITUT PASTEUR';
        }else{
            $libtaxe='rien';
        }




        $em = $this->getDoctrine()->getManager();
        $zone = $em->getRepository('AppBundle:Zone')->findOneByName($n_zone);
        $id_zone='';
        if($zone){
            $id_zone=$zone[0]->getId();
        }

        //Recuperation des tarifs

//var_dump($libtaxe);die();
        $domaine=$id_zone;
       //var_dump($client);die();

        $tarifs = $em->getRepository('AppBundle:Bareme') -> findByDocClientDomaine($documt, $client, $domaine, 'rien');

        //var_dump($tarifs);die();
        //Suppression des tarifs s'il y en a
        if( count($tarifs) > 0)
        {
            foreach($tarifs as $key => $value)
            {
                $em->remove($tarifs[$key]);
                $em->flush();
            }
        }

        if($client=='Abonne'){
            return $this->redirectToRoute('bareme_abonne', array(
                'zone' => $n_zone,
            ));
        }elseif($client=='Occasionnel'){
            return $this->redirectToRoute('bareme_occasionnel', array(
                'zone' => $n_zone,
            ));
        }else{
            return $this->redirectToRoute('bareme_national', array(
                'typeclient' => $tclient,
            ));
        }
    }






}
