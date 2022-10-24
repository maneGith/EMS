<?php

namespace AppBundle\Controller;

use AppBundle\Entity\User;
use FOS\UserBundle\Model\UserManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\JsonResponse;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;


class DefaultController extends Controller
{


    private $userManager;
    public function __construct(UserManagerInterface $userManager)
    {
        $this->userManager = $userManager;
    }
    /**
     * @Route("/", name="homepage")
     */
    public function indexAction(Request $request)
    {

        //var_dump($periode);die();
        // replace this example code with whatever you need
        return $this->render('default/index.html.twig', [
            'base_dir' => realpath($this->getParameter('kernel.project_dir')).DIRECTORY_SEPARATOR,
        ]);
    }

    /**
     * @Route("/password", name="password")
     */
    public function passwordAction(Request $request)
    {
        // replace this example code with whatever you need
        $currentPassword=  $request->get('oldpass');
        $newpass1=$request->get('newpass1');
        $newpass2=$request->get('newpass2');
        $error='';
        if($currentPassword!=null){

            $encoderService = $this->container->get('security.password_encoder');
            $userObject=$this->getUser();

            $match = $encoderService->isPasswordValid($userObject, $currentPassword);
            if(!$match){
                $error="Attention, ancien mot de passe incorrect !!!";
            }elseif($newpass1!=$newpass2){
                $error="Attention, confirmation mot de passe incorrect !!!";
            }else{

                $newpass1=$encoderService->encodePassword($userObject, $newpass1);
                $userObject->setPassword($newpass1);
                $this->userManager->updateUser($userObject);
            }
            //var_dump($match);die();
        }


        return $this->render('password.html.twig', [
            'error' => $error,
        ]);
    }

    /**
     * @Route("/design", name="design")
     */
    public function designAction(Request $request)
    {
        // replace this example code with whatever you need
        return $this->render('default/design.html.twig');
    }

    /**
     * 
     *
     * @Route("/historique", name="depots_historique")
     */
    public function historiqueAction(Request $request)
    {
         $date=date('d/m/Y');
         $annee_prec=substr($date,6)-1;
         $annee_preprec = $annee_prec-1;
         //var_dump($annee_preprec);die();

        return $this->render('default/historique.html.twig', array(
            'anneeprec' => $annee_prec,
            'anneepreprec' => $annee_preprec
            ));
    }
    
    /**
     * 
     *
     * @Route("recouv/historique", name="recouvr_index_historique")
     */
    public function historiqueindexAction(Request $request)
    {
         $date=date('d/m/Y');
         $annee_prec=substr($date,6)-1;
         $annee_preprec = $annee_prec-1;
         //var_dump($annee_preprec);die();

        return $this->render('default/historiquefacture.html.twig', array(
            'anneeprec' => $annee_prec,
            'anneepreprec' => $annee_preprec
            ));
    }

    /**
     * @Route("/listtarif", name="list_tarifs")
     */
    public function listtarifsAction(Request $request)
    {
        // replace this example code with whatever you need
        return $this->render('includes/tarifs.html.twig');
    }

    /**
     * @Route("/sendsms", name="sendsms_route")
     */
    public function sendsmsAction(Request $request)
    {

        // Account details
        $apiKey = urlencode('Your apiKey');

        // Message details
        $numbers = "+221777437444";
        $sender = urlencode('Jims Autos');
        $message = rawurlencode('This is your message');

      //  $numbers = implode(',', $numbers);

        // Prepare data for POST request
        $data = array('apikey' => $apiKey, 'numbers' => $numbers, "sender" => $sender, "message" => $message);

        // Send the POST request with cURL
        $ch = curl_init('https://api.txtlocal.com/sendsmspost.php');
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($ch);
        curl_close($ch);

        // Process your response here

        return $this->render('default/index.html.twig', [
            'reponse' => $response,
        ]);
    }

    /**
     * @Route("/chercher", name="cherche_route")
     */
    public function rechercheAction(Request $request)
    {


        return $this->render('envoi/chercher.html.twig', [

        ]);
    }


    /**
     * @Route("/agent/taxe", name="agent_determiner_taxe")
     */
    public function determinerTaxeAction(Request $request)
    {

        if($this->get('security.authorization_checker')->isGranted('ROLE_AGENTGUICHET')){


            //Recuperation des parametres d'envoi
            $client = '';
            $envoi = '';
            $document = '';
            $echelle = '';
            $id_paysdes ='';
            $nom_paysdes =  '';
            $pays=array();
            $em = $this->getDoctrine()->getManager();
            $abonnes=$em->getRepository('AppBundle:Abonne')->findByAbonnes();
            $depot = $request->get('depot');
            $msg = $request->get('msg');
            date_default_timezone_set('UTC');
            $date = $request->get('date');
             $journnee=date('d/m/Y');
            if($date!=null){
               $journnee=  $date;
            }
           
            $heuredep=date('H:i',time());
            //var_dump($envoi);die();
            $session=new Session();
            $session->set('depot', $depot);
            if($msg!='1'){
                // set and get session attributes
                $session->set('msg', '');
                $session->set('paysdes', '0');
                $session->set('poids', '');
                $session->set('envoi', '0');
                $session->set('abonne', '');
                $session->set('nomabonne', '');
            }

            if($depot=='oen'){
                $client = 'Occasionnel';
                $document = 'Tout type';
                $echelle = 'National';
                //Recuperation de id du domaine national
                $zone = $em->getRepository('AppBundle:Zone')->findOneByName('Interne');
                $id_zone=$zone[0]->getId();
                $sen=$em->getRepository('AppBundle:Pays')->findByZone($id_zone);
                $id_paysdes = $sen[0]->getId();
                $nom_paysdes =  $sen[0]->getName();
            }elseif($depot=='oei'){
                $client = 'Occasionnel';
                $document = 'Tout type';
                $echelle = 'International';
                //Recuperation de id du domaine national
                $pays = $em->getRepository('AppBundle:Pays')->findByName();

            }elseif($depot=='ren'){
                $client = 'Abonné';
                $document = 'Tout type';
                $echelle = 'National';
                //Recuperation de id du domaine national
                $zone = $em->getRepository('AppBundle:Zone')->findOneByName('Interne');
                $id_zone=$zone[0]->getId();
                $sen=$em->getRepository('AppBundle:Pays')->findByZone($id_zone);
                $id_paysdes = $sen[0]->getId();
                $nom_paysdes =  $sen[0]->getName();


            }elseif($depot=='rei'){
                $client = 'Abonné';
                $document = 'Document';
                $echelle = 'International';
                //Recuperation de id du domaine national
                $pays = $em->getRepository('AppBundle:Pays')->findByName();

            }


            // Process your response here


            if($depot=='oen' || $depot=='oei' || $depot=='ren' || $depot=='rei' ){
                return $this->render('default/AgentDeterminerTaxe.html.twig', array(
                    'pays' => $pays,
                    'document' => $document,
                    'client' => $client,
                    'echelle' => $echelle,
                    'depot' => $depot,
                    'id_paysdes' => $id_paysdes,
                    'nom_paysdes' => $nom_paysdes,
                    'abonnes'=>$abonnes,
                    'journnee' => $journnee,
                    'heure' => $heuredep,
                    'msg' => $msg,
                ));



            }else{
                return $this->redirectToRoute('homepage');
            }
        }else{
            return $this->redirectToRoute('homepage');
        }



    }



    /**
     * @Route("/agent/client", name="agent_recherche_client")
     */
    public function rechercheClientAction(Request $request)
    {
        if($this->get('security.authorization_checker')->isGranted('ROLE_AGENTGUICHET')){

            //Recuperation des parametres d'envoi
            $client = '';
            $envoi = $request->get('typeenvoi');
            $document = '';
            $id_pays =$request->get('pays');
            $nom_paysdes =  '';
            $poids = $request->get('poids');

            $em = $this->getDoctrine()->getManager();
            $abonnes=$em->getRepository('AppBundle:Abonne')->findByAbonnes();

            $session=new Session();
            $depot = $session->get('depot');
            $msg = $request->get('msg');
            if(!$msg){
                // set and get session attributes
                $session->set('paysdes', $id_pays);
                $session->set('poids', $poids);
                $session->set('envoi', $envoi);
                $session->set('msg', '');
            }else{
                $id_pays=$session->get('paysdes');
                $poids=$session->get('poids');
                $envoi=$session->get('envoi');
            }


            //var_dump($envoi);die();
            //Controle de saisie de pays et poids
            if($id_pays==0){

                $session=new Session();
                // set and get session attributes
                $session->set('msg', 'Attention : La destination est obligatoire !');

                return $this->redirectToRoute('agent_determiner_taxe', array( 'depot' => $depot,'msg' => '1'));
            }
            elseif($envoi=='0'){

                $session=new Session();
                // set and get session attributes
                $session->set('msg', 'Attention : Le type d envoi est  obligatoire');

                return $this->redirectToRoute('agent_determiner_taxe', array( 'depot' => $depot,'msg' => '1'));
            }
            elseif(!is_numeric($poids) ){

                $session=new Session();
                // set and get session attributes
                $session->set('msg', 'Attention : Le poids est un nombre réel ex: 0.00 !');
                $session->set('poids', '');

                return $this->redirectToRoute('agent_determiner_taxe', array( 'depot' => $depot,'msg' => '1'));
            }

            elseif($poids <=0 || $poids > 30){

                $session=new Session();
                // set and get session attributes
                $session->set('msg', 'Attention : Le poids est compris entre 0+ et 30 kgs !');
                $session->set('poids', '');

                return $this->redirectToRoute('agent_determiner_taxe', array( 'depot' => $depot,'msg' => '1'));
            }


            if($depot=='ren'){
                $client = 'Abonné';
                $document = 'Tout type';
                $client_bareme='Tout type';
                //Recuperation de id du domaine national
                $zone = $em->getRepository('AppBundle:Zone')->findOneByName('Interne');
                $id_zone=$zone[0]->getId();
                $sen=$em->getRepository('AppBundle:Pays')->findByZone($id_zone);
                $domaine = $sen[0]->getZone()->getId();
                $id_paysdes = $sen[0]->getId();
                $nom_paysdes =  $sen[0]->getName();
            }elseif($depot=='rei'){
                $client = 'Abonné';
                $document = 'Document';
                $client_bareme='Abonne';
                //Recuperation  du pays  destinataire
                $sen=$em->getRepository('AppBundle:Pays')->findOneById($id_pays);
                $domaine = $sen[0]->getZone()->getId();
                $id_paysdes = $sen[0]->getId();
                $nom_paysdes =  $sen[0]->getName();

            }
            // Process your response here

            $bareme1 = $em->getRepository('AppBundle:Bareme')->findByPoidsDocClientDomaine($document, $client_bareme, $domaine, $poids);
            $tarif=$bareme1[0]->getTarif();
            $tva=$bareme1[0]->getTva();
            $ttc=$bareme1[0]->getTtc();

            $session->set('tarif', $tarif);
            $session->set('tva', $tva);
            $session->set('ttc', $ttc);


            return $this->render('default/AgentRechercheClient.html.twig', array(
                    'document' => $document,
                    'client' => $client,
                    'envoi' => $envoi,
                    'depot' => $depot,
                    'id_paysdes' => $id_paysdes,
                    'nom_paysdes' => $nom_paysdes,
                    'poids' => $poids,
                    'tarif' => $tarif,
                    'tva' => $tva,
                    'ttc' => $ttc,
                    'abonnes'=>$abonnes
                )
            );
        }else{
            return $this->redirectToRoute('homepage');
        }
    }


    public  function searchBarAction(){
        $form = $this->createFormBuilder(null)
            ->add('search', TextType::class)
            ->getForm();

        return $this->render('client/SearchBar.html.twig', [
            'form' => $form->createView()
        ]);
    }


    /**
     * @Route("/agent/depot/differe", name="agent_depot_differe")
     */
    public function viewdepotdiffAction(Request $request)
    {
        $msg = $request->get('msg');
        $session=new Session();
        $date = $request->get('date');
        if(!$msg){
            // set and get session attributes
            $session->set('msg', '');
        }

        return $this->render('default/differer/diff1.html.twig', array(
            'date' => $date,
        ));
    }

    /**
     * @Route("/agent/depot", name="agent_depot")
     */
    public function depotdiffAction(Request $request)
    {
        if($this->get('security.authorization_checker')->isGranted('ROLE_AGENTGUICHET')){
            //Recuperation des parametres d'envoi
            $client = '';
            $envoi = '';
            $document = '';
            $echelle = '';
            $id_paysdes ='';
            $nom_paysdes =  '';
            $pays=array();
            $em = $this->getDoctrine()->getManager();
            $abonnes=$em->getRepository('AppBundle:Abonne')->findByAbonnes();
            $depot = $request->get('depot');
            $msg = $request->get('msg');
            $journnee=$request->get('datepicker');
            //var_dump($journnee);die();
            $heuredep=$request->get('timepicker');
            $session=new Session();
            $session->set('depot', $depot);
            if(!$msg){
                // set and get session attributes
                $session->set('msg', '');
                $session->set('paysdes', '0');
                $session->set('poids', '');
                $session->set('envoi', '0');
            }

            if(strlen($journnee)<10){
                // set and get session attributes
                $session->set('msg', "La date est incorrecte");
                return $this->redirectToRoute('agent_depot_differe', array('msg' => '1'));
            }

            if($depot=='oen'){
                $client = 'Occasionnel';
                $document = 'Tout type';
                $echelle = 'National';
                //Recuperation de id du domaine national
                $zone = $em->getRepository('AppBundle:Zone')->findOneByName('Interne');
                $id_zone=$zone[0]->getId();
                $sen=$em->getRepository('AppBundle:Pays')->findByZone($id_zone);
                $id_paysdes = $sen[0]->getId();
                $nom_paysdes =  $sen[0]->getName();
            }elseif($depot=='oei'){
                $client = 'Occasionnel';
                $document = 'Tout type';
                $echelle = 'International';
                //Recuperation de id du domaine national
                $pays = $em->getRepository('AppBundle:Pays')->findByName();

            }elseif($depot=='ren'){
                $client = 'Abonné';
                $document = 'Tout type';
                $echelle = 'National';
                //Recuperation de id du domaine national
                $zone = $em->getRepository('AppBundle:Zone')->findOneByName('Interne');
                $id_zone=$zone[0]->getId();
                $sen=$em->getRepository('AppBundle:Pays')->findByZone($id_zone);
                $id_paysdes = $sen[0]->getId();
                $nom_paysdes =  $sen[0]->getName();


            }elseif($depot=='rei'){
                $client = 'Abonné';
                $document = 'Document';
                $echelle = 'International';
                //Recuperation de id du domaine national
                $pays = $em->getRepository('AppBundle:Pays')->findByName();

            }

            // Process your response here


            if($depot=='oen' || $depot=='oei' || $depot=='ren' || $depot=='rei' ){
                return $this->render('default/AgentDeterminerTaxe.html.twig', array(
                    'pays' => $pays,
                    'document' => $document,
                    'client' => $client,
                    'echelle' => $echelle,
                    'depot' => $depot,
                    'id_paysdes' => $id_paysdes,
                    'nom_paysdes' => $nom_paysdes,
                    'abonnes'=>$abonnes,
                    'journnee' => $journnee,
                    'heure' => $heuredep,
                    'msg'=> '0'
                ));



            }else{
                return $this->redirectToRoute('homepage');
            }


        }else{
            return $this->redirectToRoute('homepage');
        }

    }

    /**
     * @Route("/agences/vacations", name="agences_vacations")
     */
    public function vacationsAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $agence = $request->get('agence');

        if($agence=='ems'){
            $agencesAG = $em->getRepository('AppBundle:Agence')->findByAG();
        }elseif($agence=='pdk'){

            $agencesAG = $em->getRepository('AppBundle:Agence')->findByPD();
        }elseif($agence=='prg'){
            $agencesAG = $em->getRepository('AppBundle:Agence')->findByPR();
        }else{
            return $this->redirectToRoute('homepage');
        }


        $date=date('d/m/Y');
        $annee_cours=substr($date, 6);

        $getmois = $request->get('mois');
        if($getmois!=null){
            $date='00/'.$getmois;

        }else{
            $date=date('d/m/Y');
        }
        $mois=substr($date, 3);
        $date='%'.substr($date, 2);


        $agences=array();
        $recup_infosagences=array();

        for ($i=0; $i<count($agencesAG); $i++){
            $recup_infosagences['agence']= $agencesAG[$i]->getNom();
            $id_agence= $agencesAG[$i]->getId();

            $bord= $em->getRepository('AppBundle:Envoi')->findByJourneeBR($date, $id_agence);
            $recup_infosagences['bordereaux']=$bord;



            //var_dump($bord);die();
            $agences[$i]=$recup_infosagences;
        }

        // var_dump($agences);die();

         return $this->render('default/bordereauxagences.html.twig', array(
            'agences' => $agences,
            'agence' => $agence,
            'mois' => $mois,
            'annee_cours' => $annee_cours,

        ));
    }


     /**
     * @Route("/agences/vacations/historique", name="agences_vacations_historique")
     */
    public function vacationshistoriqueAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $agence = $request->get('agence');

        if($agence=='ems'){
            $agencesAG = $em->getRepository('AppBundle:Agence')->findByAG();
        }elseif($agence=='pdk'){

            $agencesAG = $em->getRepository('AppBundle:Agence')->findByPD();
        }elseif($agence=='prg'){
            $agencesAG = $em->getRepository('AppBundle:Agence')->findByPR();
        }else{
            return $this->redirectToRoute('homepage');
        }


        $date=date('d/m/Y');
        $annee_cours=substr($date, 6);

        $getmois = $request->get('mois');
        
        if($getmois=='0'){
          
            return $this->redirectToRoute('depots_historique');
        }
        
        
        
        
        if($getmois!=null){
            $date='00/'.$getmois;

        }else{
            $date=date('d/m/Y');
        }
        $mois=substr($date, 3);
        $date='%'.substr($date, 2);


        $agences=array();
        $recup_infosagences=array();

        for ($i=0; $i<count($agencesAG); $i++){
            $recup_infosagences['agence']= $agencesAG[$i]->getNom();
            $id_agence= $agencesAG[$i]->getId();

            $bord= $em->getRepository('AppBundle:Envoi')->findByJourneeBR($date, $id_agence);
            $recup_infosagences['bordereaux']=$bord;



            //var_dump($bord);die();
            $agences[$i]=$recup_infosagences;
        }

        // var_dump($agences);die();

       
        
        return $this->render('default/bordereauxagenceshistorique.html.twig', array(
            'agences' => $agences,
            'agence' => $agence,
            'mois' => $mois,
            

        ));
    }

    /**
     * @Route("dates/agences/bordereaux", name="dates_agences_borderaeux")
     */
    public function bordereauxagenecesAction(Request $request)
    {

        $em = $this->getDoctrine()->getManager();
        $agence = $request->get('agence');

        if($agence=='ems'){
            $agencesAG = $em->getRepository('AppBundle:Agence')->findByAG();
        }elseif($agence=='pdk'){

            $agencesAG = $em->getRepository('AppBundle:Agence')->findByPD();
        }elseif($agence=='prg'){
            $agencesAG = $em->getRepository('AppBundle:Agence')->findByPR();
        }else{
            return $this->redirectToRoute('homepage');
        }

        $date=date('d/m/Y');
        $date='%'.substr($date, 2);


        $agences=array();
        $recup_infosagences=array();

        for ($i=0; $i<count($agencesAG); $i++){
            $recup_infosagences['agence']= $agencesAG[$i]->getNom();
            $id_agence= $agencesAG[$i]->getId();

            $bord= $em->getRepository('AppBundle:Envoi')->findByJourneeBR($date, $id_agence);
            $recup_infosagences['bordereaux']=$bord;



            //var_dump($bord);die();
            $agences[$i]=$recup_infosagences;
        }

       // var_dump($agences);die();

        return $this->render('default/bordereauxagences.html.twig', array(
            'agences' => $agences,
        ));
    }

    /**
     * @Route("/abonnes/recouvrement", name="recouvrement")
     */
    public function recouvrementAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        //Listte des clients abonnes

        $periode = $request->get('periode');

        

        $dateems=date('d/m/Y');

        $date=date('d/m/Y');



        $annee_cours=substr($date, 6);
        $annee_prece=$annee_cours-1;
        $date='%'.substr($date, 2);
        $mois=substr($date, 2);
        $indexmois=substr($date, 2, 2);
        //var_dump($date);die();
        $getmois = $request->get('mois');
        $indexannee = $request->get('annee');
        //Date du jour
        if($getmois!=null){
            $date='%/'.$getmois.'/'.$indexannee;
            $indexmois=$getmois;
            $mois=$getmois.'/'.$indexannee;
            $dateems='00/'.$getmois.'/'.$indexannee;
        }

        if($periode!=null){
            $date='%/'.$periode;
            $mois=$periode;
            $indexmois=substr($periode, 0, 2);
            $dateems='00/'.$periode;
        }

        $nmrfacture = $request->get('nmrfacture');
        if($nmrfacture!=null){

           $facturesab = $em->getRepository('AppBundle:Facture')->findByPeriodeFacturesAbonnes($periode);




            //Suppression des tarifs s'il y en a
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


$abonnes = $em->getRepository('AppBundle:Abonne')->findByAbonnes();

        $ch='22';

        $clients=array();
        $recup_infosclients=array();
        $montantgen=0;
        $tvagen=0;
        $ttcgen=0;

        $montantgenna=0;
        $tvagenna=0;
        $ttcgenna=0;

        $montantgenin=0;
        $tvagenin=0;
        $ttcgenin=0;

        for($i=0; $i<count($abonnes); $i++){
            $recup_infosclients['abonne']= $abonnes[$i];
            $id_abonne= $abonnes[$i]->getId();
            $etat=$this->isRecorved($id_abonne, $mois, 'OUI');
            $recup_infosclients['etat']=$etat;

            //Total nationnal
            $montantnational=0;
            $tvanational=0;
            $ttcnational=0;
            //Total internationnal
            $montantinternational=0;
            $tvainternational=0;
            $ttcinternational=0;

            $facture = $em->getRepository('AppBundle:Facture')->findOneByIdAbonnePeriode($id_abonne, $mois);
           // var_dump($facture);die();
            if($facture){
                $numfacture=$facture[0]->getNumfacture();
                $recup_infosclients['numfacture']=$numfacture.'/'.$mois;
            }else{
                $recup_infosclients['numfacture']='---/'.$mois;
            }

            //Total général
            $envois = $em->getRepository('AppBundle:AbonneEnvoi')->findByIdAbonneMois($id_abonne, $date);
            $montant=0;
            $tva=0;
            $ttc=0;
            for($j=0; $j<count($envois); $j++){
                $montant=  $montant+$envois[$j]->getEnvoi()->getTarif();
                $tva=  $tva+$envois[$j]->getEnvoi()->getTva();
                $ttc=  $ttc+$envois[$j]->getEnvoi()->getTtc();
                if($envois[$j]->getEnvoi()->getEchelle()=='National'){
                    $montantnational=$montantnational+$envois[$j]->getEnvoi()->getTarif();
                    $tvanational=$tvanational+$envois[$j]->getEnvoi()->getTva();
                    $ttcnational=$ttcnational+$envois[$j]->getEnvoi()->getTtc();

                    $montantgenna=$montantgenna+$envois[$j]->getEnvoi()->getTarif();
                    $tvagenna=$tvagenna+$envois[$j]->getEnvoi()->getTva();
                    $ttcgenna=$ttcgenna+$envois[$j]->getEnvoi()->getTtc();
                }else{
                    $montantinternational=$montantinternational+$envois[$j]->getEnvoi()->getTarif();
                    $tvainternational=$tvainternational+$envois[$j]->getEnvoi()->getTva();
                    $ttcinternational=$ttcinternational+$envois[$j]->getEnvoi()->getTtc();

                    $montantgenin=$montantgenin+$envois[$j]->getEnvoi()->getTarif();
                    $tvagenin=$tvagenin+$envois[$j]->getEnvoi()->getTva();
                    $ttcgenin=$ttcgenin+$envois[$j]->getEnvoi()->getTtc();

                }

                $montantgen=  $montantgen+$envois[$j]->getEnvoi()->getTarif();
                $tvagen=  $tvagen+$envois[$j]->getEnvoi()->getTva();
                $ttcgen=  $ttcgen+$envois[$j]->getEnvoi()->getTtc();
            }
            $recup_infosclients['montant']= $montant;
            $recup_infosclients['tva']= $tva;
            $recup_infosclients['ttc']= $ttc;


            $recup_infosclients['montantnational']= $montantnational;
            $recup_infosclients['tvanational']= $tvanational;
            $recup_infosclients['ttcnational']= $ttcnational;

            $recup_infosclients['montantinternational']= $montantinternational;
            $recup_infosclients['tvainternational']= $tvainternational;
            $recup_infosclients['ttcinternational']= $ttcinternational;



            $clients[$i]=$recup_infosclients;
        }

       // var_dump($abonnes);die();

        return $this->render('default/facturesagences.html.twig', array(
            'annee_cours' => $annee_cours,
            'annee_prece' => $annee_prece,
            'indexmois' => $indexmois,
            'abonnes' => $abonnes,
            'clients' => $clients,
            'date' => substr($dateems, 3),
            'mois' =>$mois,
            'montantgen' => $montantgen,
            'tvagen' => $tvagen,
            'ttcgen' => $ttcgen,

            'montantgenna' => $montantgenna,
            'tvagenna' => $tvagenna,
            'ttcgenna' => $ttcgenna,

            'montantgenin' => $montantgenin,
            'tvagenin' => $tvagenin,
            'ttcgenin' => $ttcgenin,
            'periode' => $periode,
        ));
    }


    
    /**
     * @Route("/abonnes/recouvrement/historique", name="recouvrement_historique")
     */
    public function recouvrementhistoriqueAction(Request $request)
    {
    
        $em = $this->getDoctrine()->getManager();
        //Listte des clients abonnes

      

        




      
        //var_dump($date);die();
        $getmois = $request->get('mois');
      
         if($getmois=='0'){
          if($this->get('security.authorization_checker')->isGranted('ROLE_RECOUVREMENT')){
              return $this->redirectToRoute('recouvr_index_historique'); 
          } else {
              return $this->redirectToRoute('depots_historique'); 
          }
           
        }
        
        $date='%/'.$getmois;
        $indexmois=$getmois;
        $mois=$getmois;
        $dateems='00/'.$getmois;
       

        

        $nmrfacture = $request->get('nmrfacture');
        if($nmrfacture!=null){

           $facturesab = $em->getRepository('AppBundle:Facture')->findByPeriodeFacturesAbonnes($periode);




            //Suppression des tarifs s'il y en a
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


$abonnes = $em->getRepository('AppBundle:Abonne')->findByAbonnes();

        $ch='22';

        $clients=array();
        $recup_infosclients=array();
        $montantgen=0;
        $tvagen=0;
        $ttcgen=0;

        $montantgenna=0;
        $tvagenna=0;
        $ttcgenna=0;

        $montantgenin=0;
        $tvagenin=0;
        $ttcgenin=0;

        for($i=0; $i<count($abonnes); $i++){
            $recup_infosclients['abonne']= $abonnes[$i];
            $id_abonne= $abonnes[$i]->getId();
            $etat=$this->isRecorved($id_abonne, $mois, 'OUI');
            $recup_infosclients['etat']=$etat;

            //Total nationnal
            $montantnational=0;
            $tvanational=0;
            $ttcnational=0;
            //Total internationnal
            $montantinternational=0;
            $tvainternational=0;
            $ttcinternational=0;

            $facture = $em->getRepository('AppBundle:Facture')->findOneByIdAbonnePeriode($id_abonne, $mois);
           // var_dump($facture);die();
            if($facture){
                $numfacture=$facture[0]->getNumfacture();
                $recup_infosclients['numfacture']=$numfacture.'/'.$mois;
            }else{
                $recup_infosclients['numfacture']='---/'.$mois;
            }

            //Total général
            $envois = $em->getRepository('AppBundle:AbonneEnvoi')->findByIdAbonneMois($id_abonne, $date);
            $montant=0;
            $tva=0;
            $ttc=0;
            for($j=0; $j<count($envois); $j++){
                $montant=  $montant+$envois[$j]->getEnvoi()->getTarif();
                $tva=  $tva+$envois[$j]->getEnvoi()->getTva();
                $ttc=  $ttc+$envois[$j]->getEnvoi()->getTtc();
                if($envois[$j]->getEnvoi()->getEchelle()=='National'){
                    $montantnational=$montantnational+$envois[$j]->getEnvoi()->getTarif();
                    $tvanational=$tvanational+$envois[$j]->getEnvoi()->getTva();
                    $ttcnational=$ttcnational+$envois[$j]->getEnvoi()->getTtc();

                    $montantgenna=$montantgenna+$envois[$j]->getEnvoi()->getTarif();
                    $tvagenna=$tvagenna+$envois[$j]->getEnvoi()->getTva();
                    $ttcgenna=$ttcgenna+$envois[$j]->getEnvoi()->getTtc();
                }else{
                    $montantinternational=$montantinternational+$envois[$j]->getEnvoi()->getTarif();
                    $tvainternational=$tvainternational+$envois[$j]->getEnvoi()->getTva();
                    $ttcinternational=$ttcinternational+$envois[$j]->getEnvoi()->getTtc();

                    $montantgenin=$montantgenin+$envois[$j]->getEnvoi()->getTarif();
                    $tvagenin=$tvagenin+$envois[$j]->getEnvoi()->getTva();
                    $ttcgenin=$ttcgenin+$envois[$j]->getEnvoi()->getTtc();

                }

                $montantgen=  $montantgen+$envois[$j]->getEnvoi()->getTarif();
                $tvagen=  $tvagen+$envois[$j]->getEnvoi()->getTva();
                $ttcgen=  $ttcgen+$envois[$j]->getEnvoi()->getTtc();
            }
            $recup_infosclients['montant']= $montant;
            $recup_infosclients['tva']= $tva;
            $recup_infosclients['ttc']= $ttc;


            $recup_infosclients['montantnational']= $montantnational;
            $recup_infosclients['tvanational']= $tvanational;
            $recup_infosclients['ttcnational']= $ttcnational;

            $recup_infosclients['montantinternational']= $montantinternational;
            $recup_infosclients['tvainternational']= $tvainternational;
            $recup_infosclients['ttcinternational']= $ttcinternational;



            $clients[$i]=$recup_infosclients;
        }

       // var_dump($abonnes);die();

        return $this->render('default/facturesagenceshistorique.html.twig', array(
            
            'indexmois' => $indexmois,
            'abonnes' => $abonnes,
            'clients' => $clients,
            'date' => substr($dateems, 3),
            'mois' =>$mois,
            'montantgen' => $montantgen,
            'tvagen' => $tvagen,
            'ttcgen' => $ttcgen,

            'montantgenna' => $montantgenna,
            'tvagenna' => $tvagenna,
            'ttcgenna' => $ttcgenna,

            'montantgenin' => $montantgenin,
            'tvagenin' => $tvagenin,
            'ttcgenin' => $ttcgenin,
           
        ));
    }

    /**
     * @Route("/abonne/facture", name="abonne_facture")
     */
    public function factureAction(Request $request)
    {


       // var_dump($this->trouvejourouvre("01-04-2019",-1));die();

        $em = $this->getDoctrine()->getManager();
        //param facture
        $mois=$request->get('date');

        //Dernier jour ouvrable de chaque mois
        if($mois!=null){

            $mm=substr($mois, 0, 2);
            $aa=substr($mois,  3);
            if($mm==12){
                $mm='01';
                $aa=$aa+1;
            }else{
                $mm=$mm + '1';

                if($mm<10){
                    $mm='0'.$mm;
                }
            }

            $djomois=$this->trouvejourouvre('01-'.$mm.'-'.$aa , -1);
            $djomois=str_replace("-","/", $djomois);

        }
         //var_dump($djomois);die();
        $version=$request->get('version');
        $reqmois='%'.$mois;
        $abonne = $request->get('abonne');

        $datepicker = $request->get('datepicker');
        $moddtefac=$request->get('moddtefac');

         //var_dump($mois);die();
        $tababonne=$em->getRepository('AppBundle:Abonne')->findOneById($abonne);
        $nomabonne=$tababonne[0]->getNom();
        $adresseabonne=$tababonne[0]->getAdresse();
        $id_abonne=$tababonne[0]->getId();
        $facture = $em->getRepository('AppBundle:Facture')->findOneByIdAbonnePeriode($id_abonne, $mois)[0];

        if($moddtefac=='yes'){
            $facture->setDateedition($datepicker);
            $em->persist($facture);
            $em->flush();
        }
       // var_dump($nomabonne);die();

        $envois = $em->getRepository('AppBundle:AbonneEnvoi')->findByIdAbonneMois($abonne, $reqmois);

        $envoisna = $em->getRepository('AppBundle:AbonneEnvoi')->findByIdAbonneMoisEchelle($abonne, $reqmois, 'National');
        $envoisin = $em->getRepository('AppBundle:AbonneEnvoi')->findByIdAbonneMoisEchelle($abonne, $reqmois, 'International');



        $tab=array();
        $recuptab=array();
        $totalna=0;
        $totalni=0;
        $total=0;
        $tva=0;

        $mmmois=substr($mois,0,2);
        $aaan=substr($mois,3);
        $moislettre='';
        // var_dump($mmmois);die();
        if($mmmois=='01'){
            $moislettre='Janvier '.$aaan;
        }elseif($mmmois=='02'){
            $moislettre='Février '.$aaan;
        }elseif($mmmois=='03'){
            $moislettre='Mars '.$aaan;
        }
        elseif($mmmois=='04'){
            $moislettre='Avril '.$aaan;
        }elseif($mmmois=='05'){
            $moislettre='Mai '.$aaan;
        }elseif($mmmois=='06'){
            $moislettre='Juin '.$aaan;
        }elseif($mmmois=='07'){
            $moislettre='Juillet '.$aaan;
        }elseif($mmmois=='08'){
            $moislettre='Août '.$aaan;
        }elseif($mmmois=='09'){
            $moislettre='Septembre '.$aaan;
        }elseif($mmmois=='10'){
            $moislettre='Octobre '.$aaan;
        }elseif($mmmois=='11'){
            $moislettre='Novembre '.$aaan;
        }elseif($mmmois=='12'){
            $moislettre='Décembre '.$aaan;
        }


        $recuptab['nature']='LG';
        $recuptab['date']='';
        $recuptab['id']='';
        $recuptab['agence']='';
        $recuptab['code']='';
        $recuptab['destination']='';
        $recuptab['destfacture']='';
        $recuptab['poids']='';
        $recuptab['montant']='';
        $tab[count($tab)]=$recuptab;

        $recuptab['nature']='PR';
        $recuptab['date']='';
        $recuptab['id']='';
        $recuptab['agence']='';
        $recuptab['code']=$moislettre;
        $recuptab['destination']='';
        $recuptab['destfacture']='';
        $recuptab['poids']='';
        $recuptab['montant']='';
        $tab[count($tab)]=$recuptab;

        $recuptab['nature']='NAB';
        $recuptab['date']='';
        $recuptab['id']='';
        $recuptab['agence']='';
        $recuptab['code']=$nomabonne;
        $recuptab['destination']='';
        $recuptab['destfacture']='';
        $recuptab['poids']='';
        $recuptab['montant']='';
        $tab[count($tab)]=$recuptab;

        $recuptab['nature']='AAB';
        $recuptab['date']='';
        $recuptab['id']='';
        $recuptab['agence']='';
        $recuptab['code']=$adresseabonne;
        $recuptab['destination']='';
        $recuptab['destfacture']='';
        $recuptab['poids']='';
        $recuptab['montant']='';
        $tab[count($tab)]=$recuptab;

        $recuptab['nature']='LV';
        $recuptab['date']='';
        $recuptab['id']='';
        $recuptab['agence']='';
        $recuptab['code']='';
        $recuptab['destination']='';
        $recuptab['destfacture']='';
        $recuptab['poids']='';
        $recuptab['montant']='';
        $tab[count($tab)]=$recuptab;

        $recuptab['nature']='NFA';
        $recuptab['date']='';
        $recuptab['id']='';
        $recuptab['agence']='';
        $recuptab['code']='';
        $recuptab['destination']='';
        $recuptab['destfacture']='';
        $recuptab['poids']='';
        $recuptab['montant']='';
        $tab[count($tab)]=$recuptab;

        if(count($envoisin)>=1){
            $recuptab['nature']='IN';
            $recuptab['date']='';
            $recuptab['id']='';
            $recuptab['agence']='';
            $recuptab['code']='';
            $recuptab['destination']='';
            $recuptab['destfacture']='';
            $recuptab['poids']='';
            $recuptab['montant']='';

            $tab[count($tab)]=$recuptab;

            for($i=0; $i<count($envoisin); $i++){
                $recuptab['nature']='EN';
                $recuptab['date']=$envoisin[$i]->getEnvoi()->getDate();
                $recuptab['id']=$envoisin[$i]->getEnvoi()->getId();
                $recuptab['agence']=$envoisin[$i]->getEnvoi()->getAgence()->getNom();
                $recuptab['code']=$envoisin[$i]->getEnvoi()->getCodeenvoi();
                $recuptab['destination']=$envoisin[$i]->getEnvoi()->getDestinataire()->getUsager()->getPays()->getName();
                $recuptab['destfacture']='etr';
                $recuptab['poids']=$envoisin[$i]->getEnvoi()->getPoids();
                $recuptab['montant']=$envoisin[$i]->getEnvoi()->getTarif();
                $totalni=$totalni+$envoisin[$i]->getEnvoi()->getTarif();

                $tva=$tva+$envoisin[$i]->getEnvoi()->getTva();

                $tab[count($tab)]=$recuptab;
               //var_dump($envoisin[$i]->getEnvoi()->getDestinataire()->getUsager()->getPays()->getName());die();

            }

            $recuptab['nature']='TNI';
            $recuptab['date']='';
            $recuptab['id']='';
            $recuptab['agence']='';
            $recuptab['code']='';
            $recuptab['destination']='';
            $recuptab['destfacture']='';
            $recuptab['poids']='';
            $recuptab['montant']=$totalni;

            $tab[count($tab)]=$recuptab;


        }



        if(count($envoisna)>=1){
            $recuptab['nature']='NA';
            $recuptab['date']='';
            $recuptab['id']='';
            $recuptab['agence']='';
            $recuptab['code']='';
            $recuptab['destination']='';
            $recuptab['destfacture']='';
            $recuptab['poids']='';
            $recuptab['montant']='';

            $tab[count($tab)]=$recuptab;

            for($i=0; $i<count($envoisna); $i++){
                $recuptab['nature']='EN';
                $recuptab['date']=$envoisna[$i]->getEnvoi()->getDate();
                $recuptab['id']=$envoisna[$i]->getEnvoi()->getId();
                $recuptab['agence']=$envoisna[$i]->getEnvoi()->getAgence()->getNom();
                $recuptab['code']=$envoisna[$i]->getEnvoi()->getCodeenvoi();
                
                $recuptab['destination']=$envoisna[$i]->getEnvoi()->getDestinataire()->getUsager()->getVille();
                
//                if($recuptab['code']=='RP060342710SN'){
//                    //$id=$envoisna[$i]->getEnvoi()->getDestinataire()->getUsager()->getVille();
//                  var_dump($recuptab['destination'].'yyy');die();
//  
//                }
                
                $recuptab['destfacture']=$envoisna[$i]->getEnvoi()->getDesfacture();
                $recuptab['poids']=$envoisna[$i]->getEnvoi()->getPoids();
                $recuptab['montant']=$envoisna[$i]->getEnvoi()->getTarif();
                $totalna=$totalna+$envoisna[$i]->getEnvoi()->getTarif();
                $tva=$tva+$envoisna[$i]->getEnvoi()->getTva();
                $tab[count($tab)]=$recuptab;
                //var_dump($envoisin[$i]->getEnvoi()->getDestinataire()->getUsager()->getPays()->getName());die();

            }

            $recuptab['nature']='TNA';
            $recuptab['date']='';
            $recuptab['id']='';
            $recuptab['agence']='';
            $recuptab['code']='';
            $recuptab['destination']='';
            $recuptab['destfacture']='';
            $recuptab['poids']='';
            $recuptab['montant']=$totalna;

            $tab[count($tab)]=$recuptab;


        }


        $recuptab['nature']='TT';
        $recuptab['date']='';
        $recuptab['id']='';
        $recuptab['agence']='';
        $recuptab['code']='';
        $recuptab['destination']='';
        $recuptab['destfacture']='';
        $recuptab['poids']='';
        $recuptab['montant']=$totalna+$totalni;

        $tab[count($tab)]=$recuptab;

        $recuptab['nature']='TVA';
        $recuptab['date']='';
        $recuptab['id']='';
        $recuptab['agence']='';
        $recuptab['code']='';
        $recuptab['destination']='';
        $recuptab['destfacture']='';
        $recuptab['poids']='';
        $recuptab['montant']=$tva;

        $tab[count($tab)]=$recuptab;


        $recuptab['nature']='TTC';
        $recuptab['date']='';
        $recuptab['id']='';
        $recuptab['agence']='';
        $recuptab['code']='';
        $recuptab['destination']='';
        $recuptab['destfacture']='';
        $recuptab['poids']='';
        $recuptab['montant']=$tva+$totalna+$totalni;

       //$nuts = new nuts($nombre, 'EUR');
        $formatter = \NumberFormatter::create('fr_FR', \NumberFormatter::SPELLOUT);
        $formatter->setAttribute(\NumberFormatter::FRACTION_DIGITS, 0);
        $formatter->setAttribute(\NumberFormatter::ROUNDING_MODE, \NumberFormatter::ROUND_HALFUP);

         // un million cinq cent vingt-deux mille cinq cent trente
           $ttlenlettres=$formatter->format($recuptab['montant']);

        $tab[count($tab)]=$recuptab;

        $recuptab['nature']='TAC';
        $recuptab['date']='';
        $recuptab['id']='';
        $recuptab['agence']='';
        $recuptab['code']='';
        $recuptab['destination']='';
        $recuptab['destfacture']='';
        $recuptab['poids']='';
        $recuptab['montant']=$tva+$totalna+$totalni;

        $tab[count($tab)]=$recuptab;

        $recuptab['nature']='TAL';
        $recuptab['date']='';
        $recuptab['id']='';
        $recuptab['agence']='';
        $recuptab['code']='';
        $recuptab['destination']='';
        $recuptab['destfacture']='';
        $recuptab['poids']='';
        $recuptab['montant']=$tva+$totalna+$totalni;

        $tab[count($tab)]=$recuptab;



        $recuptab['nature']='CDT';
        $recuptab['date']='';
        $recuptab['id']='';
        $recuptab['agence']='';
        $recuptab['code']='';
        $recuptab['destination']='';
        $recuptab['destfacture']='';
        $recuptab['poids']='';
        $recuptab['montant']='';
        $tab[count($tab)]=$recuptab;

        $recuptab['nature']='DLM';
        $recuptab['date']='';
        $recuptab['id']='';
        $recuptab['agence']='';
        $recuptab['code']='';
        $recuptab['destination']='';
        $recuptab['destfacture']='';
        $recuptab['poids']='';
        $recuptab['montant']='';
        $tab[count($tab)]=$recuptab;

        $recuptab['nature']='VRM';
        $recuptab['date']='';
        $recuptab['id']='';
        $recuptab['agence']='';
        $recuptab['code']='';
        $recuptab['destination']='';
        $recuptab['destfacture']='';
        $recuptab['poids']='';
        $recuptab['montant']='';
        $tab[count($tab)]=$recuptab;


        $recuptab['nature']='LV';
        $recuptab['date']='';
        $recuptab['id']='';
        $recuptab['agence']='';
        $recuptab['code']='';
        $recuptab['destination']='';
        $recuptab['destfacture']='';
        $recuptab['poids']='';
        $recuptab['montant']='';
        $tab[count($tab)]=$recuptab;

        $recuptab['nature']='ACQ';
        $recuptab['date']='';
        $recuptab['id']='';
        $recuptab['agence']='';
        $recuptab['code']='';
        $recuptab['destination']='';
        $recuptab['destfacture']='';
        $recuptab['poids']='';
        $recuptab['montant']='';
        $tab[count($tab)]=$recuptab;

        //var_dump(count($tab));die();


        $recuptab['nature']='LV';
        $recuptab['date']='';
        $recuptab['id']='';
        $recuptab['agence']='';
        $recuptab['code']='';
        $recuptab['destination']='';
        $recuptab['destfacture']='';
        $recuptab['poids']='';
        $recuptab['montant']='';
        $tab[count($tab)]=$recuptab;

        $recuptab['nature']='LV';
        $recuptab['date']='';
        $recuptab['id']='';
        $recuptab['agence']='';
        $recuptab['code']='';
        $recuptab['destination']='';
        $recuptab['destfacture']='';
        $recuptab['poids']='';
        $recuptab['montant']='';
        $tab[count($tab)]=$recuptab;

        $recuptab['nature']='LV';
        $recuptab['date']='';
        $recuptab['id']='';
        $recuptab['agence']='';
        $recuptab['code']='';
        $recuptab['destination']='';
        $recuptab['destfacture']='';
        $recuptab['poids']='';
        $recuptab['montant']='';
        $tab[count($tab)]=$recuptab;

        $recuptab['nature']='LAU1';
        $recuptab['date']='';
        $recuptab['id']='';
        $recuptab['agence']='';
        $recuptab['code']='';
        $recuptab['destination']='';
        $recuptab['destfacture']='';
        $recuptab['poids']='';
        $recuptab['montant']='';
        $tab[count($tab)]=$recuptab;

        $recuptab['nature']='LAU2';
        $recuptab['date']='';
        $recuptab['id']='';
        $recuptab['agence']='';
        $recuptab['code']='';
        $recuptab['destination']='';
        $recuptab['destfacture']='';
        $recuptab['poids']='';
        $recuptab['montant']='';
        $tab[count($tab)]=$recuptab;


        $recuptab['nature']='LV';
        $recuptab['date']='';
        $recuptab['id']='';
        $recuptab['agence']='';
        $recuptab['code']='';
        $recuptab['destination']='';
        $recuptab['destfacture']='';
        $recuptab['poids']='';
        $recuptab['montant']='';
        $tab[count($tab)]=$recuptab;

        $recuptab['nature']='LV';
        $recuptab['date']='';
        $recuptab['id']='';
        $recuptab['agence']='';
        $recuptab['code']='';
        $recuptab['destination']='';
        $recuptab['destfacture']='';
        $recuptab['poids']='';
        $recuptab['montant']='';
        $tab[count($tab)]=$recuptab;

        $recuptab['nature']='LV';
        $recuptab['date']='';
        $recuptab['id']='';
        $recuptab['agence']='';
        $recuptab['code']='';
        $recuptab['destination']='';
        $recuptab['destfacture']='';
        $recuptab['poids']='';
        $recuptab['montant']='';
        $tab[count($tab)]=$recuptab;

        $recuptab['nature']='LAU3';
        $recuptab['date']='';
        $recuptab['id']='';
        $recuptab['agence']='';
        $recuptab['code']='';
        $recuptab['destination']='';
        $recuptab['destfacture']='';
        $recuptab['poids']='';
        $recuptab['montant']='';
        $tab[count($tab)]=$recuptab;

        $nppage= (count($tab)-count($tab)%43)/43+1;
        //var_dump($nppage);die();


        $mod=43-count($tab)%43+1;
        for($i=1; $i<=$mod; $i++)
        {
            $recuptab['nature']='LV';
            $recuptab['date']='';
            $recuptab['id']='';
            $recuptab['agence']='';
            $recuptab['code']='';
            $recuptab['destination']='';
            $recuptab['destfacture']='';
            $recuptab['poids']='';
            $recuptab['montant']='';
            $tab[count($tab)]=$recuptab;
        }



        //var_dump(count($tab));die();
        //Autorités pour signature


        $titre1='DGTITULAIRE';
        $titre2='DGINTERIM';
        $autDG = $em->getRepository('AppBundle:Autorites')->findByTitreActif($titre1, $titre2);
        if(count($autDG)>0){
            $autDG = $autDG[0];
        }else{
            $autDG =null;
        }


        $titre1='DAFCTITULAIRE';
        $titre2='DAFCINTERIM';
        $autDAFC = $em->getRepository('AppBundle:Autorites')->findByTitreActif($titre1, $titre2);
        if(count($autDAFC)>0){
            $autDAFC = $autDAFC[0];
        }else{
            $autDAFC =null;
        }





        if($mois==null){
            return $this->redirectToRoute('homepage');
        }

        return $this->render('default/factute.html.twig', array(
            'mois' => $mois,
            'nomabonne' => $nomabonne,
            'adresse' => $adresseabonne,
            'envois' => $tab,
            'facture' => $facture,
            'abonne' =>$abonne,
            'version' =>$version,
            'nppage' =>$nppage,
            'autDG'  =>  $autDG,
            'autDAFC'  => $autDAFC,
            'ttlettre'=>$ttlenlettres,
            'djomois'=>$djomois
        ));
    }

    /**
     * @Route("/depots/mois", name="depots_mois")
     */
    public function depotsmoisAction(Request $request)
    {

        $em = $this->getDoctrine()->getManager();
        $date=date('d/m/Y');
        $annee_cours=substr($date, 6);
        $annee_prece=$annee_cours-1;
        $date='%'.substr($date, 2);
        $mois=substr($date, 2);
        $indexmois=substr($date, 2, 2);
        //var_dump($date);die();
        $getmois = $request->get('mois');
        $indexannee = $request->get('annee');
        //Date du jour
        if($getmois!=null){
            $date='%/'.$getmois.'/'.$indexannee;
            $indexmois=$getmois;
            $mois=$getmois.'/'.$indexannee;
        }

        $agence = $request->get('agence');
        if($agence=='ems'){
            $agencesAG = $em->getRepository('AppBundle:Agence')->findByAG();
        }elseif($agence=='pdk'){

            $agencesAG = $em->getRepository('AppBundle:Agence')->findByPD();
        }elseif($agence=='prg'){
            $agencesAG = $em->getRepository('AppBundle:Agence')->findByPR();
        }else{
            return $this->redirectToRoute('homepage');
        }


        //Liste des agences EMS

        //var_dump($agencesAG);die();

        $infosagences=array();
        $recup_infosagences=array();
        //Récupération des données
        $totalRNG=array('Nbr' => '0','Poids' => '0','Montant' => '0','TVA' => '0');
        $totalONG=array('Nbr' => '0','Poids' => '0','Montant' => '0','TVA' => '0');

        $totalRIG=array('Nbr' => '0','Poids' => '0','Montant' => '0','TVA' => '0');
        $totalOIG=array('Nbr' => '0','Poids' => '0','Montant' => '0','TVA' => '0');

        $totalNG=array('Nbr' => '0','Poids' => '0','Montant' => '0','TVA' => '0');
        $totalIG=array('Nbr' => '0','Poids' => '0','Montant' => '0','TVA' => '0');

        $totalRG=array('Nbr' => '0','Poids' => '0','Montant' => '0','TVA' => '0');
        $totalOG=array('Nbr' => '0','Poids' => '0','Montant' => '0','TVA' => '0');

        $totalROG=array('Nbr' => '0','Poids' => '0','Montant' => '0','TVA' => '0');

        for ($i=0; $i<count($agencesAG); $i++){
            $recup_infosagences['agence']= $agencesAG[$i];
            $id_agence= $agencesAG[$i]->getId();

            $totalR=array('Nbr' => '0','Poids' => '0','Montant' => '0','TVA' => '0');
            $totalO=array('Nbr' => '0','Poids' => '0','Montant' => '0','TVA' => '0');
            $totalRO=array('Nbr' => '0','Poids' => '0','Montant' => '0','TVA' => '0');
            //Situation nationale clients reguliers et occasionnels
            $echelle='National';
            $reg='Abonné';
            $occ='Occasionnel';
            $totalnationR=array('Nbr' => '0','Poids' => '0','Montant' => '0','TVA' => '0');
            $totalnationO=array('Nbr' => '0','Poids' => '0','Montant' => '0','TVA' => '0');
            $totalnationRO=array('Nbr' => '0','Poids' => '0','Montant' => '0','TVA' => '0');
            $depotsreg = $em->getRepository('AppBundle:Envoi')->findByAgence($id_agence, $reg, $date, $echelle);
            $depotsocc = $em->getRepository('AppBundle:Envoi')->findByAgence($id_agence, $occ, $date, $echelle);

            for($j=0; $j<count($depotsreg); $j++){
                $totalnationR['Nbr']=  $totalnationR['Nbr']+1;
                $totalnationR['Poids']=  $totalnationR['Poids']+ $depotsreg[$j]->getPoids();
                $totalnationR['Montant']=  $totalnationR['Montant']+ $depotsreg[$j]->getTarif();
                $totalnationR['TVA']=  $totalnationR['TVA']+ $depotsreg[$j]->getTva();
            }
            $recup_infosagences['snragence']= $totalnationR;

            $totalRNG['Nbr']=  $totalRNG['Nbr']+$totalnationR['Nbr'];
            $totalRNG['Poids']= $totalRNG['Poids']+ $totalnationR['Poids'];
            $totalRNG['Montant']=  $totalRNG['Montant']+$totalnationR['Montant'];
            $totalRNG['TVA']=  $totalRNG['TVA']+$totalnationR['TVA'];

            for($j=0; $j<count($depotsocc); $j++){
                $totalnationO['Nbr']=  $totalnationO['Nbr']+1;
                $totalnationO['Poids']=  $totalnationO['Poids']+ $depotsocc[$j]->getPoids();
                $totalnationO['Montant']=  $totalnationO['Montant']+ $depotsocc[$j]->getTarif();
                $totalnationO['TVA']=  $totalnationO['TVA']+ $depotsocc[$j]->getTva();
            }
            $recup_infosagences['snoagence']= $totalnationO;

            $totalONG['Nbr']=  $totalONG['Nbr']+$totalnationO['Nbr'];
            $totalONG['Poids']= $totalONG['Poids']+ $totalnationO['Poids'];
            $totalONG['Montant']=  $totalONG['Montant']+$totalnationO['Montant'];
            $totalONG['TVA']=  $totalONG['TVA']+$totalnationO['TVA'];


            $totalnationRO['Nbr']=      $totalnationR['Nbr']+$totalnationO['Nbr'];
            $totalnationRO['Poids']=    $totalnationR['Poids']+$totalnationO['Poids'];
            $totalnationRO['Montant']=  $totalnationR['Montant']+$totalnationO['Montant'];
            $totalnationRO['TVA']=      $totalnationR['TVA']+$totalnationO['TVA'];
            $recup_infosagences['snroagence']= $totalnationRO;


            $totalNG['Nbr']=      $totalRNG['Nbr']+$totalONG['Nbr'];
            $totalNG['Poids']=    $totalRNG['Poids']+$totalONG['Poids'];
            $totalNG['Montant']=  $totalRNG['Montant']+$totalONG['Montant'];
            $totalNG['TVA']=      $totalRNG['TVA']+$totalONG['TVA'];



            //Situation internationale clients reguliers et occasionnels
            $echelle='International';
            $reg='Abonné';
            $occ='Occasionnel';
            $totalinterR=array('Nbr' => '0','Poids' => '0','Montant' => '0','TVA' => '0');
            $totalinterO=array('Nbr' => '0','Poids' => '0','Montant' => '0','TVA' => '0');
            $totalinterRO=array('Nbr' => '0','Poids' => '0','Montant' => '0','TVA' => '0');
            $depotsreg = $em->getRepository('AppBundle:Envoi')->findByAgence($id_agence, $reg, $date, $echelle);
            $depotsocc = $em->getRepository('AppBundle:Envoi')->findByAgence($id_agence, $occ, $date, $echelle);

            for($j=0; $j<count($depotsreg); $j++){
                $totalinterR['Nbr']=  $totalinterR['Nbr']+1;
                $totalinterR['Poids']=  $totalinterR['Poids']+ $depotsreg[$j]->getPoids();
                $totalinterR['Montant']=  $totalinterR['Montant']+ $depotsreg[$j]->getTarif();
                $totalinterR['TVA']=  $totalinterR['TVA']+ $depotsreg[$j]->getTva();
            }
            $recup_infosagences['siragence']= $totalinterR;

            $totalRIG['Nbr']=  $totalRIG['Nbr']+$totalinterR['Nbr'];
            $totalRIG['Poids']= $totalRIG['Poids']+ $totalinterR['Poids'];
            $totalRIG['Montant']=  $totalRIG['Montant']+$totalinterR['Montant'];
            $totalRIG['TVA']=  $totalRIG['TVA']+$totalinterR['TVA'];


            for($j=0; $j<count($depotsocc); $j++){
                $totalinterO['Nbr']=  $totalinterO['Nbr']+1;
                $totalinterO['Poids']=  $totalinterO['Poids']+ $depotsocc[$j]->getPoids();
                $totalinterO['Montant']=  $totalinterO['Montant']+ $depotsocc[$j]->getTarif();
                $totalinterO['TVA']=  $totalinterO['TVA']+ $depotsocc[$j]->getTva();
            }
            $recup_infosagences['sioagence']= $totalinterO;


            $totalOIG['Nbr']=  $totalOIG['Nbr']+$totalinterO['Nbr'];
            $totalOIG['Poids']= $totalOIG['Poids']+ $totalinterO['Poids'];
            $totalOIG['Montant']=  $totalOIG['Montant']+$totalinterO['Montant'];
            $totalOIG['TVA']=  $totalOIG['TVA']+$totalinterO['TVA'];


            $totalIG['Nbr']=      $totalRIG['Nbr']+$totalOIG['Nbr'];
            $totalIG['Poids']=    $totalRIG['Poids']+$totalOIG['Poids'];
            $totalIG['Montant']=  $totalRIG['Montant']+$totalOIG['Montant'];
            $totalIG['TVA']=      $totalRIG['TVA']+$totalOIG['TVA'];

            $totalinterRO['Nbr']=      $totalinterR['Nbr']+$totalinterO['Nbr'];
            $totalinterRO['Poids']=    $totalinterR['Poids']+$totalinterO['Poids'];
            $totalinterRO['Montant']=  $totalinterR['Montant']+$totalinterO['Montant'];
            $totalinterRO['TVA']=      $totalinterR['TVA']+$totalinterO['TVA'];
            $recup_infosagences['siroagence']= $totalinterRO;


            $totalR['Nbr']=      $totalnationR['Nbr']+$totalinterR['Nbr'];
            $totalR['Poids']=    $totalnationR['Poids']+$totalinterR['Poids'];
            $totalR['Montant']=  $totalnationR['Montant']+$totalinterR['Montant'];
            $totalR['TVA']=      $totalnationR['TVA']+$totalinterR['TVA'];
            $recup_infosagences['sragence']= $totalR;

            $totalO['Nbr']=      $totalnationO['Nbr']+$totalinterO['Nbr'];
            $totalO['Poids']=    $totalnationO['Poids']+$totalinterO['Poids'];
            $totalO['Montant']=  $totalnationO['Montant']+$totalinterO['Montant'];
            $totalO['TVA']=      $totalnationO['TVA']+$totalinterO['TVA'];
            $recup_infosagences['soagence']= $totalO;


            $totalRO['Nbr']=      $totalR['Nbr']+$totalO['Nbr'];
            $totalRO['Poids']=    $totalR['Poids']+$totalO['Poids'];
            $totalRO['Montant']=  $totalR['Montant']+$totalO['Montant'];
            $totalRO['TVA']=      $totalR['TVA']+$totalO['TVA'];
            $recup_infosagences['sroagence']= $totalRO;


            $totalRG['Nbr']=      $totalRNG['Nbr']+$totalRIG['Nbr'];
            $totalRG['Poids']=    $totalRNG['Poids']+$totalRIG['Poids'];
            $totalRG['Montant']=  $totalRNG['Montant']+$totalRIG['Montant'];
            $totalRG['TVA']=      $totalRNG['TVA']+$totalRIG['TVA'];

            $totalOG['Nbr']=      $totalONG['Nbr']+$totalOIG['Nbr'];
            $totalOG['Poids']=    $totalONG['Poids']+$totalOIG['Poids'];
            $totalOG['Montant']=  $totalONG['Montant']+$totalOIG['Montant'];
            $totalOG['TVA']=      $totalONG['TVA']+$totalOIG['TVA'];

            $totalROG['Nbr']=      $totalRG['Nbr']+$totalOG['Nbr'];
            $totalROG['Poids']=    $totalRG['Poids']+$totalOG['Poids'];
            $totalROG['Montant']=  $totalRG['Montant']+$totalOG['Montant'];
            $totalROG['TVA']=      $totalRG['TVA']+$totalOG['TVA'];













            $agences[$i]=$recup_infosagences;
        }


        return $this->render('default/DepotsOccasinnels.html.twig', array(
            'annee_cours' => $annee_cours,
            'annee_prece' => $annee_prece,
            'indexmois' => $indexmois,
            'mois' => $mois,
            'agence'  => $agence,
            'agences' => $agences,
            'totalRNG' => $totalRNG,
            'totalONG' => $totalONG,
            'totalRIG' => $totalRIG,
            'totalOIG' => $totalOIG,
            'totalNG' => $totalNG,
            'totalIG' => $totalIG,
            'totalRG' => $totalRG,
            'totalOG' => $totalOG,
            'totalROG' => $totalROG,
        ));
    }
    
    
    
    
    
    
    /**
     * @Route("/depots/mois/historique", name="depots_mois_historique")
     */
    public function depotsmoishistoriqueAction(Request $request)
    {

        $em = $this->getDoctrine()->getManager();
       
        $getmois = $request->get('mois');
        if($getmois=='0'){
          
            return $this->redirectToRoute('depots_historique');
        }
        $indexannee=substr($getmois, 3);
       
        //Date du jour  if($getmois!=null){
            $date='%/'.$getmois;
            $indexmois=$getmois;
            $mois=$getmois;
            
            // var_dump($mois);die();
       

        $agence = $request->get('agence');
        if($agence=='ems'){
            $agencesAG = $em->getRepository('AppBundle:Agence')->findByAG();
        }elseif($agence=='pdk'){

            $agencesAG = $em->getRepository('AppBundle:Agence')->findByPD();
        }elseif($agence=='prg'){
            $agencesAG = $em->getRepository('AppBundle:Agence')->findByPR();
        }else{
            return $this->redirectToRoute('homepage');
        }


        //Liste des agences EMS

        //var_dump($agencesAG);die();

        $infosagences=array();
        $recup_infosagences=array();
        //Récupération des données
        $totalRNG=array('Nbr' => '0','Poids' => '0','Montant' => '0','TVA' => '0');
        $totalONG=array('Nbr' => '0','Poids' => '0','Montant' => '0','TVA' => '0');

        $totalRIG=array('Nbr' => '0','Poids' => '0','Montant' => '0','TVA' => '0');
        $totalOIG=array('Nbr' => '0','Poids' => '0','Montant' => '0','TVA' => '0');

        $totalNG=array('Nbr' => '0','Poids' => '0','Montant' => '0','TVA' => '0');
        $totalIG=array('Nbr' => '0','Poids' => '0','Montant' => '0','TVA' => '0');

        $totalRG=array('Nbr' => '0','Poids' => '0','Montant' => '0','TVA' => '0');
        $totalOG=array('Nbr' => '0','Poids' => '0','Montant' => '0','TVA' => '0');

        $totalROG=array('Nbr' => '0','Poids' => '0','Montant' => '0','TVA' => '0');

        for ($i=0; $i<count($agencesAG); $i++){
            $recup_infosagences['agence']= $agencesAG[$i];
            $id_agence= $agencesAG[$i]->getId();

            $totalR=array('Nbr' => '0','Poids' => '0','Montant' => '0','TVA' => '0');
            $totalO=array('Nbr' => '0','Poids' => '0','Montant' => '0','TVA' => '0');
            $totalRO=array('Nbr' => '0','Poids' => '0','Montant' => '0','TVA' => '0');
            //Situation nationale clients reguliers et occasionnels
            $echelle='National';
            $reg='Abonné';
            $occ='Occasionnel';
            $totalnationR=array('Nbr' => '0','Poids' => '0','Montant' => '0','TVA' => '0');
            $totalnationO=array('Nbr' => '0','Poids' => '0','Montant' => '0','TVA' => '0');
            $totalnationRO=array('Nbr' => '0','Poids' => '0','Montant' => '0','TVA' => '0');
            $depotsreg = $em->getRepository('AppBundle:Envoi')->findByAgence($id_agence, $reg, $date, $echelle);
            $depotsocc = $em->getRepository('AppBundle:Envoi')->findByAgence($id_agence, $occ, $date, $echelle);

            for($j=0; $j<count($depotsreg); $j++){
                $totalnationR['Nbr']=  $totalnationR['Nbr']+1;
                $totalnationR['Poids']=  $totalnationR['Poids']+ $depotsreg[$j]->getPoids();
                $totalnationR['Montant']=  $totalnationR['Montant']+ $depotsreg[$j]->getTarif();
                $totalnationR['TVA']=  $totalnationR['TVA']+ $depotsreg[$j]->getTva();
            }
            $recup_infosagences['snragence']= $totalnationR;

            $totalRNG['Nbr']=  $totalRNG['Nbr']+$totalnationR['Nbr'];
            $totalRNG['Poids']= $totalRNG['Poids']+ $totalnationR['Poids'];
            $totalRNG['Montant']=  $totalRNG['Montant']+$totalnationR['Montant'];
            $totalRNG['TVA']=  $totalRNG['TVA']+$totalnationR['TVA'];

            for($j=0; $j<count($depotsocc); $j++){
                $totalnationO['Nbr']=  $totalnationO['Nbr']+1;
                $totalnationO['Poids']=  $totalnationO['Poids']+ $depotsocc[$j]->getPoids();
                $totalnationO['Montant']=  $totalnationO['Montant']+ $depotsocc[$j]->getTarif();
                $totalnationO['TVA']=  $totalnationO['TVA']+ $depotsocc[$j]->getTva();
            }
            $recup_infosagences['snoagence']= $totalnationO;

            $totalONG['Nbr']=  $totalONG['Nbr']+$totalnationO['Nbr'];
            $totalONG['Poids']= $totalONG['Poids']+ $totalnationO['Poids'];
            $totalONG['Montant']=  $totalONG['Montant']+$totalnationO['Montant'];
            $totalONG['TVA']=  $totalONG['TVA']+$totalnationO['TVA'];


            $totalnationRO['Nbr']=      $totalnationR['Nbr']+$totalnationO['Nbr'];
            $totalnationRO['Poids']=    $totalnationR['Poids']+$totalnationO['Poids'];
            $totalnationRO['Montant']=  $totalnationR['Montant']+$totalnationO['Montant'];
            $totalnationRO['TVA']=      $totalnationR['TVA']+$totalnationO['TVA'];
            $recup_infosagences['snroagence']= $totalnationRO;


            $totalNG['Nbr']=      $totalRNG['Nbr']+$totalONG['Nbr'];
            $totalNG['Poids']=    $totalRNG['Poids']+$totalONG['Poids'];
            $totalNG['Montant']=  $totalRNG['Montant']+$totalONG['Montant'];
            $totalNG['TVA']=      $totalRNG['TVA']+$totalONG['TVA'];



            //Situation internationale clients reguliers et occasionnels
            $echelle='International';
            $reg='Abonné';
            $occ='Occasionnel';
            $totalinterR=array('Nbr' => '0','Poids' => '0','Montant' => '0','TVA' => '0');
            $totalinterO=array('Nbr' => '0','Poids' => '0','Montant' => '0','TVA' => '0');
            $totalinterRO=array('Nbr' => '0','Poids' => '0','Montant' => '0','TVA' => '0');
            $depotsreg = $em->getRepository('AppBundle:Envoi')->findByAgence($id_agence, $reg, $date, $echelle);
            $depotsocc = $em->getRepository('AppBundle:Envoi')->findByAgence($id_agence, $occ, $date, $echelle);

            for($j=0; $j<count($depotsreg); $j++){
                $totalinterR['Nbr']=  $totalinterR['Nbr']+1;
                $totalinterR['Poids']=  $totalinterR['Poids']+ $depotsreg[$j]->getPoids();
                $totalinterR['Montant']=  $totalinterR['Montant']+ $depotsreg[$j]->getTarif();
                $totalinterR['TVA']=  $totalinterR['TVA']+ $depotsreg[$j]->getTva();
            }
            $recup_infosagences['siragence']= $totalinterR;

            $totalRIG['Nbr']=  $totalRIG['Nbr']+$totalinterR['Nbr'];
            $totalRIG['Poids']= $totalRIG['Poids']+ $totalinterR['Poids'];
            $totalRIG['Montant']=  $totalRIG['Montant']+$totalinterR['Montant'];
            $totalRIG['TVA']=  $totalRIG['TVA']+$totalinterR['TVA'];


            for($j=0; $j<count($depotsocc); $j++){
                $totalinterO['Nbr']=  $totalinterO['Nbr']+1;
                $totalinterO['Poids']=  $totalinterO['Poids']+ $depotsocc[$j]->getPoids();
                $totalinterO['Montant']=  $totalinterO['Montant']+ $depotsocc[$j]->getTarif();
                $totalinterO['TVA']=  $totalinterO['TVA']+ $depotsocc[$j]->getTva();
            }
            $recup_infosagences['sioagence']= $totalinterO;


            $totalOIG['Nbr']=  $totalOIG['Nbr']+$totalinterO['Nbr'];
            $totalOIG['Poids']= $totalOIG['Poids']+ $totalinterO['Poids'];
            $totalOIG['Montant']=  $totalOIG['Montant']+$totalinterO['Montant'];
            $totalOIG['TVA']=  $totalOIG['TVA']+$totalinterO['TVA'];


            $totalIG['Nbr']=      $totalRIG['Nbr']+$totalOIG['Nbr'];
            $totalIG['Poids']=    $totalRIG['Poids']+$totalOIG['Poids'];
            $totalIG['Montant']=  $totalRIG['Montant']+$totalOIG['Montant'];
            $totalIG['TVA']=      $totalRIG['TVA']+$totalOIG['TVA'];

            $totalinterRO['Nbr']=      $totalinterR['Nbr']+$totalinterO['Nbr'];
            $totalinterRO['Poids']=    $totalinterR['Poids']+$totalinterO['Poids'];
            $totalinterRO['Montant']=  $totalinterR['Montant']+$totalinterO['Montant'];
            $totalinterRO['TVA']=      $totalinterR['TVA']+$totalinterO['TVA'];
            $recup_infosagences['siroagence']= $totalinterRO;


            $totalR['Nbr']=      $totalnationR['Nbr']+$totalinterR['Nbr'];
            $totalR['Poids']=    $totalnationR['Poids']+$totalinterR['Poids'];
            $totalR['Montant']=  $totalnationR['Montant']+$totalinterR['Montant'];
            $totalR['TVA']=      $totalnationR['TVA']+$totalinterR['TVA'];
            $recup_infosagences['sragence']= $totalR;

            $totalO['Nbr']=      $totalnationO['Nbr']+$totalinterO['Nbr'];
            $totalO['Poids']=    $totalnationO['Poids']+$totalinterO['Poids'];
            $totalO['Montant']=  $totalnationO['Montant']+$totalinterO['Montant'];
            $totalO['TVA']=      $totalnationO['TVA']+$totalinterO['TVA'];
            $recup_infosagences['soagence']= $totalO;


            $totalRO['Nbr']=      $totalR['Nbr']+$totalO['Nbr'];
            $totalRO['Poids']=    $totalR['Poids']+$totalO['Poids'];
            $totalRO['Montant']=  $totalR['Montant']+$totalO['Montant'];
            $totalRO['TVA']=      $totalR['TVA']+$totalO['TVA'];
            $recup_infosagences['sroagence']= $totalRO;


            $totalRG['Nbr']=      $totalRNG['Nbr']+$totalRIG['Nbr'];
            $totalRG['Poids']=    $totalRNG['Poids']+$totalRIG['Poids'];
            $totalRG['Montant']=  $totalRNG['Montant']+$totalRIG['Montant'];
            $totalRG['TVA']=      $totalRNG['TVA']+$totalRIG['TVA'];

            $totalOG['Nbr']=      $totalONG['Nbr']+$totalOIG['Nbr'];
            $totalOG['Poids']=    $totalONG['Poids']+$totalOIG['Poids'];
            $totalOG['Montant']=  $totalONG['Montant']+$totalOIG['Montant'];
            $totalOG['TVA']=      $totalONG['TVA']+$totalOIG['TVA'];

            $totalROG['Nbr']=      $totalRG['Nbr']+$totalOG['Nbr'];
            $totalROG['Poids']=    $totalRG['Poids']+$totalOG['Poids'];
            $totalROG['Montant']=  $totalRG['Montant']+$totalOG['Montant'];
            $totalROG['TVA']=      $totalRG['TVA']+$totalOG['TVA'];













            $agences[$i]=$recup_infosagences;
        }


        return $this->render('default/DepotsOccasinnelsHistorique.html.twig', array(
            
            'indexmois' => $indexmois,
            'mois' => $mois,
            'agence'  => $agence,
            'agences' => $agences,
            'totalRNG' => $totalRNG,
            'totalONG' => $totalONG,
            'totalRIG' => $totalRIG,
            'totalOIG' => $totalOIG,
            'totalNG' => $totalNG,
            'totalIG' => $totalIG,
            'totalRG' => $totalRG,
            'totalOG' => $totalOG,
            'totalROG' => $totalROG,
        ));
    }

    
    /**
     * @Route("/depots/annee/historique", name="depots_annee_historique")
     */
    public function depotsanneehistoriqueAction(Request $request)
    {

        $em = $this->getDoctrine()->getManager();
      
        $getmois = $request->get('mois');
       
        if($getmois=='0'){
          
            return $this->redirectToRoute('depots_historique');
        }
          //var_dump($getmois);die();
        $indexannee=substr($getmois, 3);
       
        //Date du jour  if($getmois!=null){
            $date='%/'.$getmois;
            $indexmois=$getmois;
            $mois=$getmois;
            
            // var_dump($mois);die();
       

        $agence = $request->get('agence');
        if($agence=='ems'){
            $agencesAG = $em->getRepository('AppBundle:Agence')->findByAG();
        }elseif($agence=='pdk'){

            $agencesAG = $em->getRepository('AppBundle:Agence')->findByPD();
        }elseif($agence=='prg'){
            $agencesAG = $em->getRepository('AppBundle:Agence')->findByPR();
        }else{
            return $this->redirectToRoute('homepage');
        }


        //Liste des agences EMS

        //var_dump($agencesAG);die();

        $infosagences=array();
        $recup_infosagences=array();
        //Récupération des données
        $totalRNG=array('Nbr' => '0','Poids' => '0','Montant' => '0','TVA' => '0');
        $totalONG=array('Nbr' => '0','Poids' => '0','Montant' => '0','TVA' => '0');

        $totalRIG=array('Nbr' => '0','Poids' => '0','Montant' => '0','TVA' => '0');
        $totalOIG=array('Nbr' => '0','Poids' => '0','Montant' => '0','TVA' => '0');

        $totalNG=array('Nbr' => '0','Poids' => '0','Montant' => '0','TVA' => '0');
        $totalIG=array('Nbr' => '0','Poids' => '0','Montant' => '0','TVA' => '0');

        $totalRG=array('Nbr' => '0','Poids' => '0','Montant' => '0','TVA' => '0');
        $totalOG=array('Nbr' => '0','Poids' => '0','Montant' => '0','TVA' => '0');

        $totalROG=array('Nbr' => '0','Poids' => '0','Montant' => '0','TVA' => '0');

        for ($i=0; $i<count($agencesAG); $i++){
            $recup_infosagences['agence']= $agencesAG[$i];
            $id_agence= $agencesAG[$i]->getId();

            $totalR=array('Nbr' => '0','Poids' => '0','Montant' => '0','TVA' => '0');
            $totalO=array('Nbr' => '0','Poids' => '0','Montant' => '0','TVA' => '0');
            $totalRO=array('Nbr' => '0','Poids' => '0','Montant' => '0','TVA' => '0');
            //Situation nationale clients reguliers et occasionnels
            $echelle='National';
            $reg='Abonné';
            $occ='Occasionnel';
            $totalnationR=array('Nbr' => '0','Poids' => '0','Montant' => '0','TVA' => '0');
            $totalnationO=array('Nbr' => '0','Poids' => '0','Montant' => '0','TVA' => '0');
            $totalnationRO=array('Nbr' => '0','Poids' => '0','Montant' => '0','TVA' => '0');
            $depotsreg = $em->getRepository('AppBundle:Envoi')->findByAgence($id_agence, $reg, $date, $echelle);
            $depotsocc = $em->getRepository('AppBundle:Envoi')->findByAgence($id_agence, $occ, $date, $echelle);

            for($j=0; $j<count($depotsreg); $j++){
                $totalnationR['Nbr']=  $totalnationR['Nbr']+1;
                $totalnationR['Poids']=  $totalnationR['Poids']+ $depotsreg[$j]->getPoids();
                $totalnationR['Montant']=  $totalnationR['Montant']+ $depotsreg[$j]->getTarif();
                $totalnationR['TVA']=  $totalnationR['TVA']+ $depotsreg[$j]->getTva();
            }
            $recup_infosagences['snragence']= $totalnationR;

            $totalRNG['Nbr']=  $totalRNG['Nbr']+$totalnationR['Nbr'];
            $totalRNG['Poids']= $totalRNG['Poids']+ $totalnationR['Poids'];
            $totalRNG['Montant']=  $totalRNG['Montant']+$totalnationR['Montant'];
            $totalRNG['TVA']=  $totalRNG['TVA']+$totalnationR['TVA'];

            for($j=0; $j<count($depotsocc); $j++){
                $totalnationO['Nbr']=  $totalnationO['Nbr']+1;
                $totalnationO['Poids']=  $totalnationO['Poids']+ $depotsocc[$j]->getPoids();
                $totalnationO['Montant']=  $totalnationO['Montant']+ $depotsocc[$j]->getTarif();
                $totalnationO['TVA']=  $totalnationO['TVA']+ $depotsocc[$j]->getTva();
            }
            $recup_infosagences['snoagence']= $totalnationO;

            $totalONG['Nbr']=  $totalONG['Nbr']+$totalnationO['Nbr'];
            $totalONG['Poids']= $totalONG['Poids']+ $totalnationO['Poids'];
            $totalONG['Montant']=  $totalONG['Montant']+$totalnationO['Montant'];
            $totalONG['TVA']=  $totalONG['TVA']+$totalnationO['TVA'];


            $totalnationRO['Nbr']=      $totalnationR['Nbr']+$totalnationO['Nbr'];
            $totalnationRO['Poids']=    $totalnationR['Poids']+$totalnationO['Poids'];
            $totalnationRO['Montant']=  $totalnationR['Montant']+$totalnationO['Montant'];
            $totalnationRO['TVA']=      $totalnationR['TVA']+$totalnationO['TVA'];
            $recup_infosagences['snroagence']= $totalnationRO;


            $totalNG['Nbr']=      $totalRNG['Nbr']+$totalONG['Nbr'];
            $totalNG['Poids']=    $totalRNG['Poids']+$totalONG['Poids'];
            $totalNG['Montant']=  $totalRNG['Montant']+$totalONG['Montant'];
            $totalNG['TVA']=      $totalRNG['TVA']+$totalONG['TVA'];



            //Situation internationale clients reguliers et occasionnels
            $echelle='International';
            $reg='Abonné';
            $occ='Occasionnel';
            $totalinterR=array('Nbr' => '0','Poids' => '0','Montant' => '0','TVA' => '0');
            $totalinterO=array('Nbr' => '0','Poids' => '0','Montant' => '0','TVA' => '0');
            $totalinterRO=array('Nbr' => '0','Poids' => '0','Montant' => '0','TVA' => '0');
            $depotsreg = $em->getRepository('AppBundle:Envoi')->findByAgence($id_agence, $reg, $date, $echelle);
            $depotsocc = $em->getRepository('AppBundle:Envoi')->findByAgence($id_agence, $occ, $date, $echelle);

            for($j=0; $j<count($depotsreg); $j++){
                $totalinterR['Nbr']=  $totalinterR['Nbr']+1;
                $totalinterR['Poids']=  $totalinterR['Poids']+ $depotsreg[$j]->getPoids();
                $totalinterR['Montant']=  $totalinterR['Montant']+ $depotsreg[$j]->getTarif();
                $totalinterR['TVA']=  $totalinterR['TVA']+ $depotsreg[$j]->getTva();
            }
            $recup_infosagences['siragence']= $totalinterR;

            $totalRIG['Nbr']=  $totalRIG['Nbr']+$totalinterR['Nbr'];
            $totalRIG['Poids']= $totalRIG['Poids']+ $totalinterR['Poids'];
            $totalRIG['Montant']=  $totalRIG['Montant']+$totalinterR['Montant'];
            $totalRIG['TVA']=  $totalRIG['TVA']+$totalinterR['TVA'];


            for($j=0; $j<count($depotsocc); $j++){
                $totalinterO['Nbr']=  $totalinterO['Nbr']+1;
                $totalinterO['Poids']=  $totalinterO['Poids']+ $depotsocc[$j]->getPoids();
                $totalinterO['Montant']=  $totalinterO['Montant']+ $depotsocc[$j]->getTarif();
                $totalinterO['TVA']=  $totalinterO['TVA']+ $depotsocc[$j]->getTva();
            }
            $recup_infosagences['sioagence']= $totalinterO;


            $totalOIG['Nbr']=  $totalOIG['Nbr']+$totalinterO['Nbr'];
            $totalOIG['Poids']= $totalOIG['Poids']+ $totalinterO['Poids'];
            $totalOIG['Montant']=  $totalOIG['Montant']+$totalinterO['Montant'];
            $totalOIG['TVA']=  $totalOIG['TVA']+$totalinterO['TVA'];


            $totalIG['Nbr']=      $totalRIG['Nbr']+$totalOIG['Nbr'];
            $totalIG['Poids']=    $totalRIG['Poids']+$totalOIG['Poids'];
            $totalIG['Montant']=  $totalRIG['Montant']+$totalOIG['Montant'];
            $totalIG['TVA']=      $totalRIG['TVA']+$totalOIG['TVA'];

            $totalinterRO['Nbr']=      $totalinterR['Nbr']+$totalinterO['Nbr'];
            $totalinterRO['Poids']=    $totalinterR['Poids']+$totalinterO['Poids'];
            $totalinterRO['Montant']=  $totalinterR['Montant']+$totalinterO['Montant'];
            $totalinterRO['TVA']=      $totalinterR['TVA']+$totalinterO['TVA'];
            $recup_infosagences['siroagence']= $totalinterRO;


            $totalR['Nbr']=      $totalnationR['Nbr']+$totalinterR['Nbr'];
            $totalR['Poids']=    $totalnationR['Poids']+$totalinterR['Poids'];
            $totalR['Montant']=  $totalnationR['Montant']+$totalinterR['Montant'];
            $totalR['TVA']=      $totalnationR['TVA']+$totalinterR['TVA'];
            $recup_infosagences['sragence']= $totalR;

            $totalO['Nbr']=      $totalnationO['Nbr']+$totalinterO['Nbr'];
            $totalO['Poids']=    $totalnationO['Poids']+$totalinterO['Poids'];
            $totalO['Montant']=  $totalnationO['Montant']+$totalinterO['Montant'];
            $totalO['TVA']=      $totalnationO['TVA']+$totalinterO['TVA'];
            $recup_infosagences['soagence']= $totalO;


            $totalRO['Nbr']=      $totalR['Nbr']+$totalO['Nbr'];
            $totalRO['Poids']=    $totalR['Poids']+$totalO['Poids'];
            $totalRO['Montant']=  $totalR['Montant']+$totalO['Montant'];
            $totalRO['TVA']=      $totalR['TVA']+$totalO['TVA'];
            $recup_infosagences['sroagence']= $totalRO;


            $totalRG['Nbr']=      $totalRNG['Nbr']+$totalRIG['Nbr'];
            $totalRG['Poids']=    $totalRNG['Poids']+$totalRIG['Poids'];
            $totalRG['Montant']=  $totalRNG['Montant']+$totalRIG['Montant'];
            $totalRG['TVA']=      $totalRNG['TVA']+$totalRIG['TVA'];

            $totalOG['Nbr']=      $totalONG['Nbr']+$totalOIG['Nbr'];
            $totalOG['Poids']=    $totalONG['Poids']+$totalOIG['Poids'];
            $totalOG['Montant']=  $totalONG['Montant']+$totalOIG['Montant'];
            $totalOG['TVA']=      $totalONG['TVA']+$totalOIG['TVA'];

            $totalROG['Nbr']=      $totalRG['Nbr']+$totalOG['Nbr'];
            $totalROG['Poids']=    $totalRG['Poids']+$totalOG['Poids'];
            $totalROG['Montant']=  $totalRG['Montant']+$totalOG['Montant'];
            $totalROG['TVA']=      $totalRG['TVA']+$totalOG['TVA'];













            $agences[$i]=$recup_infosagences;
        }


        return $this->render('default/DepotsOccasinnelsHistoriqueAnnee.html.twig', array(
            
            'indexmois' => $indexmois,
            'mois' => $mois,
            'agence'  => $agence,
            'agences' => $agences,
            'totalRNG' => $totalRNG,
            'totalONG' => $totalONG,
            'totalRIG' => $totalRIG,
            'totalOIG' => $totalOIG,
            'totalNG' => $totalNG,
            'totalIG' => $totalIG,
            'totalRG' => $totalRG,
            'totalOG' => $totalOG,
            'totalROG' => $totalROG,
        ));
    }

    /**
     * @Route("/recap/depots", name="recap_depots")
     */
    public function depotsrecapAction(Request $request)
    {

        $em = $this->getDoctrine()->getManager();
        $date=date('d/m/Y');
        $annee_cours=substr($date, 6);
        $annee_prece=$annee_cours-1;
        $date='%'.substr($date, 2);
        $mois=substr($date, 2);
        $indexmois=substr($date, 2, 2);
        //var_dump($date);die();
        $getmois = $request->get('mois');
        $indexannee = $request->get('annee');
        //Date du jour
        if($getmois!=null){
            $date='%/'.$getmois.'/'.$indexannee;
            $indexmois=$getmois;
            $mois=$getmois.'/'.$indexannee;
        }




        $infosagences=array();
        $recup_infosagences=array();
        //Récupération des données
        $totalRNG=array('Nbr' => '0','Poids' => '0','Montant' => '0','TVA' => '0');
        $totalONG=array('Nbr' => '0','Poids' => '0','Montant' => '0','TVA' => '0');

        $totalRIG=array('Nbr' => '0','Poids' => '0','Montant' => '0','TVA' => '0');
        $totalOIG=array('Nbr' => '0','Poids' => '0','Montant' => '0','TVA' => '0');

        $totalNG=array('Nbr' => '0','Poids' => '0','Montant' => '0','TVA' => '0');
        $totalIG=array('Nbr' => '0','Poids' => '0','Montant' => '0','TVA' => '0');

        $totalRG=array('Nbr' => '0','Poids' => '0','Montant' => '0','TVA' => '0');
        $totalOG=array('Nbr' => '0','Poids' => '0','Montant' => '0','TVA' => '0');

        $totalROG=array('Nbr' => '0','Poids' => '0','Montant' => '0','TVA' => '0');


        for ($i=0; $i<4; $i++){

            //Initialisation tranche de poids
            if($i==0){
                $recup_infosagences['agence']='poids <=  0,5 kg';
            }
            if($i==1){
                $recup_infosagences['agence']='0,5 kg < poids  <= 2 kg';
            }
            if($i==2){
                $recup_infosagences['agence']='2 kg  < poids  <= 20 kg';
            }
            if($i==3){
                $recup_infosagences['agence']='poids > 20 kg';
            }


            $totalR=array('Nbr' => '0','Poids' => '0','Montant' => '0','TVA' => '0');
            $totalO=array('Nbr' => '0','Poids' => '0','Montant' => '0','TVA' => '0');
            $totalRO=array('Nbr' => '0','Poids' => '0','Montant' => '0','TVA' => '0');
            //Situation nationale clients reguliers et occasionnels
            $echelle='National';
            $reg='Abonné';
            $occ='Occasionnel';
            $totalnationR=array('Nbr' => '0','Poids' => '0','Montant' => '0','TVA' => '0');
            $totalnationO=array('Nbr' => '0','Poids' => '0','Montant' => '0','TVA' => '0');
            $totalnationRO=array('Nbr' => '0','Poids' => '0','Montant' => '0','TVA' => '0');



            if($i==0){
                $depotsreg = $em->getRepository('AppBundle:Envoi')->findByEchelonun($reg, $date, $echelle);
                $depotsocc = $em->getRepository('AppBundle:Envoi')->findByEchelonun($occ, $date, $echelle);
            }
            if($i==1){
                $depotsreg = $em->getRepository('AppBundle:Envoi')->findByEchelondeux($reg, $date, $echelle);
                $depotsocc = $em->getRepository('AppBundle:Envoi')->findByEchelondeux($occ, $date, $echelle);
            }
            if($i==2){
                $depotsreg = $em->getRepository('AppBundle:Envoi')->findByEchelontrois($reg, $date, $echelle);
                $depotsocc = $em->getRepository('AppBundle:Envoi')->findByEchelontrois($occ, $date, $echelle);
            }
            if($i==3){
                $depotsreg = $em->getRepository('AppBundle:Envoi')->findByEchelonquatre($reg, $date, $echelle);
                $depotsocc = $em->getRepository('AppBundle:Envoi')->findByEchelonquatre($occ, $date, $echelle);
            }

            for($j=0; $j<count($depotsreg); $j++){
                $totalnationR['Nbr']=  $totalnationR['Nbr']+1;
                $totalnationR['Poids']=  $totalnationR['Poids']+ $depotsreg[$j]->getPoids();
                $totalnationR['Montant']=  $totalnationR['Montant']+ $depotsreg[$j]->getTarif();
                $totalnationR['TVA']=  $totalnationR['TVA']+ $depotsreg[$j]->getTva();
            }
            $recup_infosagences['snragence']= $totalnationR;

            $totalRNG['Nbr']=  $totalRNG['Nbr']+$totalnationR['Nbr'];
            $totalRNG['Poids']= $totalRNG['Poids']+ $totalnationR['Poids'];
            $totalRNG['Montant']=  $totalRNG['Montant']+$totalnationR['Montant'];
            $totalRNG['TVA']=  $totalRNG['TVA']+$totalnationR['TVA'];

            for($j=0; $j<count($depotsocc); $j++){
                $totalnationO['Nbr']=  $totalnationO['Nbr']+1;
                $totalnationO['Poids']=  $totalnationO['Poids']+ $depotsocc[$j]->getPoids();
                $totalnationO['Montant']=  $totalnationO['Montant']+ $depotsocc[$j]->getTarif();
                $totalnationO['TVA']=  $totalnationO['TVA']+ $depotsocc[$j]->getTva();
            }
            $recup_infosagences['snoagence']= $totalnationO;

            $totalONG['Nbr']=  $totalONG['Nbr']+$totalnationO['Nbr'];
            $totalONG['Poids']= $totalONG['Poids']+ $totalnationO['Poids'];
            $totalONG['Montant']=  $totalONG['Montant']+$totalnationO['Montant'];
            $totalONG['TVA']=  $totalONG['TVA']+$totalnationO['TVA'];


            $totalnationRO['Nbr']=      $totalnationR['Nbr']+$totalnationO['Nbr'];
            $totalnationRO['Poids']=    $totalnationR['Poids']+$totalnationO['Poids'];
            $totalnationRO['Montant']=  $totalnationR['Montant']+$totalnationO['Montant'];
            $totalnationRO['TVA']=      $totalnationR['TVA']+$totalnationO['TVA'];
            $recup_infosagences['snroagence']= $totalnationRO;


            $totalNG['Nbr']=      $totalRNG['Nbr']+$totalONG['Nbr'];
            $totalNG['Poids']=    $totalRNG['Poids']+$totalONG['Poids'];
            $totalNG['Montant']=  $totalRNG['Montant']+$totalONG['Montant'];
            $totalNG['TVA']=      $totalRNG['TVA']+$totalONG['TVA'];



            //Situation internationale clients reguliers et occasionnels
            $echelle='International';
            $reg='Abonné';
            $occ='Occasionnel';
            $totalinterR=array('Nbr' => '0','Poids' => '0','Montant' => '0','TVA' => '0');
            $totalinterO=array('Nbr' => '0','Poids' => '0','Montant' => '0','TVA' => '0');
            $totalinterRO=array('Nbr' => '0','Poids' => '0','Montant' => '0','TVA' => '0');

            if($i==0){
                $depotsreg = $em->getRepository('AppBundle:Envoi')->findByEchelonun($reg, $date, $echelle);
                $depotsocc = $em->getRepository('AppBundle:Envoi')->findByEchelonun($occ, $date, $echelle);
            }
            if($i==1){
                $depotsreg = $em->getRepository('AppBundle:Envoi')->findByEchelondeux($reg, $date, $echelle);
                $depotsocc = $em->getRepository('AppBundle:Envoi')->findByEchelondeux($occ, $date, $echelle);
            }
            if($i==2){
                $depotsreg = $em->getRepository('AppBundle:Envoi')->findByEchelontrois($reg, $date, $echelle);
                $depotsocc = $em->getRepository('AppBundle:Envoi')->findByEchelontrois($occ, $date, $echelle);
            }
            if($i==3){
                $depotsreg = $em->getRepository('AppBundle:Envoi')->findByEchelonquatre($reg, $date, $echelle);
                $depotsocc = $em->getRepository('AppBundle:Envoi')->findByEchelonquatre($occ, $date, $echelle);
            }
            
            for($j=0; $j<count($depotsreg); $j++){
                $totalinterR['Nbr']=  $totalinterR['Nbr']+1;
                $totalinterR['Poids']=  $totalinterR['Poids']+ $depotsreg[$j]->getPoids();
                $totalinterR['Montant']=  $totalinterR['Montant']+ $depotsreg[$j]->getTarif();
                $totalinterR['TVA']=  $totalinterR['TVA']+ $depotsreg[$j]->getTva();
            }
            $recup_infosagences['siragence']= $totalinterR;

            $totalRIG['Nbr']=  $totalRIG['Nbr']+$totalinterR['Nbr'];
            $totalRIG['Poids']= $totalRIG['Poids']+ $totalinterR['Poids'];
            $totalRIG['Montant']=  $totalRIG['Montant']+$totalinterR['Montant'];
            $totalRIG['TVA']=  $totalRIG['TVA']+$totalinterR['TVA'];


            for($j=0; $j<count($depotsocc); $j++){
                $totalinterO['Nbr']=  $totalinterO['Nbr']+1;
                $totalinterO['Poids']=  $totalinterO['Poids']+ $depotsocc[$j]->getPoids();
                $totalinterO['Montant']=  $totalinterO['Montant']+ $depotsocc[$j]->getTarif();
                $totalinterO['TVA']=  $totalinterO['TVA']+ $depotsocc[$j]->getTva();
            }
            $recup_infosagences['sioagence']= $totalinterO;


            $totalOIG['Nbr']=  $totalOIG['Nbr']+$totalinterO['Nbr'];
            $totalOIG['Poids']= $totalOIG['Poids']+ $totalinterO['Poids'];
            $totalOIG['Montant']=  $totalOIG['Montant']+$totalinterO['Montant'];
            $totalOIG['TVA']=  $totalOIG['TVA']+$totalinterO['TVA'];


            $totalIG['Nbr']=      $totalRIG['Nbr']+$totalOIG['Nbr'];
            $totalIG['Poids']=    $totalRIG['Poids']+$totalOIG['Poids'];
            $totalIG['Montant']=  $totalRIG['Montant']+$totalOIG['Montant'];
            $totalIG['TVA']=      $totalRIG['TVA']+$totalOIG['TVA'];

            $totalinterRO['Nbr']=      $totalinterR['Nbr']+$totalinterO['Nbr'];
            $totalinterRO['Poids']=    $totalinterR['Poids']+$totalinterO['Poids'];
            $totalinterRO['Montant']=  $totalinterR['Montant']+$totalinterO['Montant'];
            $totalinterRO['TVA']=      $totalinterR['TVA']+$totalinterO['TVA'];
            $recup_infosagences['siroagence']= $totalinterRO;


            $totalR['Nbr']=      $totalnationR['Nbr']+$totalinterR['Nbr'];
            $totalR['Poids']=    $totalnationR['Poids']+$totalinterR['Poids'];
            $totalR['Montant']=  $totalnationR['Montant']+$totalinterR['Montant'];
            $totalR['TVA']=      $totalnationR['TVA']+$totalinterR['TVA'];
            $recup_infosagences['sragence']= $totalR;

            $totalO['Nbr']=      $totalnationO['Nbr']+$totalinterO['Nbr'];
            $totalO['Poids']=    $totalnationO['Poids']+$totalinterO['Poids'];
            $totalO['Montant']=  $totalnationO['Montant']+$totalinterO['Montant'];
            $totalO['TVA']=      $totalnationO['TVA']+$totalinterO['TVA'];
            $recup_infosagences['soagence']= $totalO;


            $totalRO['Nbr']=      $totalR['Nbr']+$totalO['Nbr'];
            $totalRO['Poids']=    $totalR['Poids']+$totalO['Poids'];
            $totalRO['Montant']=  $totalR['Montant']+$totalO['Montant'];
            $totalRO['TVA']=      $totalR['TVA']+$totalO['TVA'];
            $recup_infosagences['sroagence']= $totalRO;


            $totalRG['Nbr']=      $totalRNG['Nbr']+$totalRIG['Nbr'];
            $totalRG['Poids']=    $totalRNG['Poids']+$totalRIG['Poids'];
            $totalRG['Montant']=  $totalRNG['Montant']+$totalRIG['Montant'];
            $totalRG['TVA']=      $totalRNG['TVA']+$totalRIG['TVA'];

            $totalOG['Nbr']=      $totalONG['Nbr']+$totalOIG['Nbr'];
            $totalOG['Poids']=    $totalONG['Poids']+$totalOIG['Poids'];
            $totalOG['Montant']=  $totalONG['Montant']+$totalOIG['Montant'];
            $totalOG['TVA']=      $totalONG['TVA']+$totalOIG['TVA'];

            $totalROG['Nbr']=      $totalRG['Nbr']+$totalOG['Nbr'];
            $totalROG['Poids']=    $totalRG['Poids']+$totalOG['Poids'];
            $totalROG['Montant']=  $totalRG['Montant']+$totalOG['Montant'];
            $totalROG['TVA']=      $totalRG['TVA']+$totalOG['TVA'];





            $agences[$i]=$recup_infosagences;
        }
//var_dump($agences);die();


        return $this->render('default/recapdepots.html.twig', array(
            'annee_cours' => $annee_cours,
            'annee_prece' => $annee_prece,
            'indexmois' => $indexmois,
            'mois' => $mois,
            'agences' => $agences,
            'totalRNG' => $totalRNG,
            'totalONG' => $totalONG,
            'totalRIG' => $totalRIG,
            'totalOIG' => $totalOIG,
            'totalNG' => $totalNG,
            'totalIG' => $totalIG,
            'totalRG' => $totalRG,
            'totalOG' => $totalOG,
            'totalROG' => $totalROG,
        ));
    }
    
    
  /**
     * @Route("/recap/depots/historique", name="recap_depots_historique")
     */
    public function depotsrecaphistoriqueAction(Request $request)
    {  

        $em = $this->getDoctrine()->getManager();
       
        //var_dump($date);die();
        $getmois = $request->get('mois');
        if($getmois=='0'){
          
            return $this->redirectToRoute('depots_historique');
        }
        $indexannee =  $indexannee=substr($getmois, 3);
        

            $date='%/'.$getmois;
            $indexmois=$getmois;
            $mois=$getmois;

        $infosagences=array();
        $recup_infosagences=array();
        //Récupération des données
        $totalRNG=array('Nbr' => '0','Poids' => '0','Montant' => '0','TVA' => '0');
        $totalONG=array('Nbr' => '0','Poids' => '0','Montant' => '0','TVA' => '0');

        $totalRIG=array('Nbr' => '0','Poids' => '0','Montant' => '0','TVA' => '0');
        $totalOIG=array('Nbr' => '0','Poids' => '0','Montant' => '0','TVA' => '0');

        $totalNG=array('Nbr' => '0','Poids' => '0','Montant' => '0','TVA' => '0');
        $totalIG=array('Nbr' => '0','Poids' => '0','Montant' => '0','TVA' => '0');

        $totalRG=array('Nbr' => '0','Poids' => '0','Montant' => '0','TVA' => '0');
        $totalOG=array('Nbr' => '0','Poids' => '0','Montant' => '0','TVA' => '0');

        $totalROG=array('Nbr' => '0','Poids' => '0','Montant' => '0','TVA' => '0');


        for ($i=0; $i<4; $i++){

            //Initialisation tranche de poids
            if($i==0){
                $recup_infosagences['agence']='poids <=  0,5 kg';
            }
            if($i==1){
                $recup_infosagences['agence']='0,5 kg < poids  <= 2 kg';
            }
            if($i==2){
                $recup_infosagences['agence']='2 kg  < poids  <= 20 kg';
            }
            if($i==3){
                $recup_infosagences['agence']='poids > 20 kg';
            }


            $totalR=array('Nbr' => '0','Poids' => '0','Montant' => '0','TVA' => '0');
            $totalO=array('Nbr' => '0','Poids' => '0','Montant' => '0','TVA' => '0');
            $totalRO=array('Nbr' => '0','Poids' => '0','Montant' => '0','TVA' => '0');
            //Situation nationale clients reguliers et occasionnels
            $echelle='National';
            $reg='Abonné';
            $occ='Occasionnel';
            $totalnationR=array('Nbr' => '0','Poids' => '0','Montant' => '0','TVA' => '0');
            $totalnationO=array('Nbr' => '0','Poids' => '0','Montant' => '0','TVA' => '0');
            $totalnationRO=array('Nbr' => '0','Poids' => '0','Montant' => '0','TVA' => '0');



            if($i==0){
                $depotsreg = $em->getRepository('AppBundle:Envoi')->findByEchelonun($reg, $date, $echelle);
                $depotsocc = $em->getRepository('AppBundle:Envoi')->findByEchelonun($occ, $date, $echelle);
            }
            if($i==1){
                $depotsreg = $em->getRepository('AppBundle:Envoi')->findByEchelondeux($reg, $date, $echelle);
                $depotsocc = $em->getRepository('AppBundle:Envoi')->findByEchelondeux($occ, $date, $echelle);
            }
            if($i==2){
                $depotsreg = $em->getRepository('AppBundle:Envoi')->findByEchelontrois($reg, $date, $echelle);
                $depotsocc = $em->getRepository('AppBundle:Envoi')->findByEchelontrois($occ, $date, $echelle);
            }
            if($i==3){
                $depotsreg = $em->getRepository('AppBundle:Envoi')->findByEchelonquatre($reg, $date, $echelle);
                $depotsocc = $em->getRepository('AppBundle:Envoi')->findByEchelonquatre($occ, $date, $echelle);
            }

            for($j=0; $j<count($depotsreg); $j++){
                $totalnationR['Nbr']=  $totalnationR['Nbr']+1;
                $totalnationR['Poids']=  $totalnationR['Poids']+ $depotsreg[$j]->getPoids();
                $totalnationR['Montant']=  $totalnationR['Montant']+ $depotsreg[$j]->getTarif();
                $totalnationR['TVA']=  $totalnationR['TVA']+ $depotsreg[$j]->getTva();
            }
            $recup_infosagences['snragence']= $totalnationR;

            $totalRNG['Nbr']=  $totalRNG['Nbr']+$totalnationR['Nbr'];
            $totalRNG['Poids']= $totalRNG['Poids']+ $totalnationR['Poids'];
            $totalRNG['Montant']=  $totalRNG['Montant']+$totalnationR['Montant'];
            $totalRNG['TVA']=  $totalRNG['TVA']+$totalnationR['TVA'];

            for($j=0; $j<count($depotsocc); $j++){
                $totalnationO['Nbr']=  $totalnationO['Nbr']+1;
                $totalnationO['Poids']=  $totalnationO['Poids']+ $depotsocc[$j]->getPoids();
                $totalnationO['Montant']=  $totalnationO['Montant']+ $depotsocc[$j]->getTarif();
                $totalnationO['TVA']=  $totalnationO['TVA']+ $depotsocc[$j]->getTva();
            }
            $recup_infosagences['snoagence']= $totalnationO;

            $totalONG['Nbr']=  $totalONG['Nbr']+$totalnationO['Nbr'];
            $totalONG['Poids']= $totalONG['Poids']+ $totalnationO['Poids'];
            $totalONG['Montant']=  $totalONG['Montant']+$totalnationO['Montant'];
            $totalONG['TVA']=  $totalONG['TVA']+$totalnationO['TVA'];


            $totalnationRO['Nbr']=      $totalnationR['Nbr']+$totalnationO['Nbr'];
            $totalnationRO['Poids']=    $totalnationR['Poids']+$totalnationO['Poids'];
            $totalnationRO['Montant']=  $totalnationR['Montant']+$totalnationO['Montant'];
            $totalnationRO['TVA']=      $totalnationR['TVA']+$totalnationO['TVA'];
            $recup_infosagences['snroagence']= $totalnationRO;


            $totalNG['Nbr']=      $totalRNG['Nbr']+$totalONG['Nbr'];
            $totalNG['Poids']=    $totalRNG['Poids']+$totalONG['Poids'];
            $totalNG['Montant']=  $totalRNG['Montant']+$totalONG['Montant'];
            $totalNG['TVA']=      $totalRNG['TVA']+$totalONG['TVA'];



            //Situation internationale clients reguliers et occasionnels
            $echelle='International';
            $reg='Abonné';
            $occ='Occasionnel';
            $totalinterR=array('Nbr' => '0','Poids' => '0','Montant' => '0','TVA' => '0');
            $totalinterO=array('Nbr' => '0','Poids' => '0','Montant' => '0','TVA' => '0');
            $totalinterRO=array('Nbr' => '0','Poids' => '0','Montant' => '0','TVA' => '0');

            if($i==0){
                $depotsreg = $em->getRepository('AppBundle:Envoi')->findByEchelonun($reg, $date, $echelle);
                $depotsocc = $em->getRepository('AppBundle:Envoi')->findByEchelonun($occ, $date, $echelle);
            }
            if($i==1){
                $depotsreg = $em->getRepository('AppBundle:Envoi')->findByEchelondeux($reg, $date, $echelle);
                $depotsocc = $em->getRepository('AppBundle:Envoi')->findByEchelondeux($occ, $date, $echelle);
            }
            if($i==2){
                $depotsreg = $em->getRepository('AppBundle:Envoi')->findByEchelontrois($reg, $date, $echelle);
                $depotsocc = $em->getRepository('AppBundle:Envoi')->findByEchelontrois($occ, $date, $echelle);
            }
            if($i==3){
                $depotsreg = $em->getRepository('AppBundle:Envoi')->findByEchelonquatre($reg, $date, $echelle);
                $depotsocc = $em->getRepository('AppBundle:Envoi')->findByEchelonquatre($occ, $date, $echelle);
            }
            
            for($j=0; $j<count($depotsreg); $j++){
                $totalinterR['Nbr']=  $totalinterR['Nbr']+1;
                $totalinterR['Poids']=  $totalinterR['Poids']+ $depotsreg[$j]->getPoids();
                $totalinterR['Montant']=  $totalinterR['Montant']+ $depotsreg[$j]->getTarif();
                $totalinterR['TVA']=  $totalinterR['TVA']+ $depotsreg[$j]->getTva();
            }
            $recup_infosagences['siragence']= $totalinterR;

            $totalRIG['Nbr']=  $totalRIG['Nbr']+$totalinterR['Nbr'];
            $totalRIG['Poids']= $totalRIG['Poids']+ $totalinterR['Poids'];
            $totalRIG['Montant']=  $totalRIG['Montant']+$totalinterR['Montant'];
            $totalRIG['TVA']=  $totalRIG['TVA']+$totalinterR['TVA'];


            for($j=0; $j<count($depotsocc); $j++){
                $totalinterO['Nbr']=  $totalinterO['Nbr']+1;
                $totalinterO['Poids']=  $totalinterO['Poids']+ $depotsocc[$j]->getPoids();
                $totalinterO['Montant']=  $totalinterO['Montant']+ $depotsocc[$j]->getTarif();
                $totalinterO['TVA']=  $totalinterO['TVA']+ $depotsocc[$j]->getTva();
            }
            $recup_infosagences['sioagence']= $totalinterO;


            $totalOIG['Nbr']=  $totalOIG['Nbr']+$totalinterO['Nbr'];
            $totalOIG['Poids']= $totalOIG['Poids']+ $totalinterO['Poids'];
            $totalOIG['Montant']=  $totalOIG['Montant']+$totalinterO['Montant'];
            $totalOIG['TVA']=  $totalOIG['TVA']+$totalinterO['TVA'];


            $totalIG['Nbr']=      $totalRIG['Nbr']+$totalOIG['Nbr'];
            $totalIG['Poids']=    $totalRIG['Poids']+$totalOIG['Poids'];
            $totalIG['Montant']=  $totalRIG['Montant']+$totalOIG['Montant'];
            $totalIG['TVA']=      $totalRIG['TVA']+$totalOIG['TVA'];

            $totalinterRO['Nbr']=      $totalinterR['Nbr']+$totalinterO['Nbr'];
            $totalinterRO['Poids']=    $totalinterR['Poids']+$totalinterO['Poids'];
            $totalinterRO['Montant']=  $totalinterR['Montant']+$totalinterO['Montant'];
            $totalinterRO['TVA']=      $totalinterR['TVA']+$totalinterO['TVA'];
            $recup_infosagences['siroagence']= $totalinterRO;


            $totalR['Nbr']=      $totalnationR['Nbr']+$totalinterR['Nbr'];
            $totalR['Poids']=    $totalnationR['Poids']+$totalinterR['Poids'];
            $totalR['Montant']=  $totalnationR['Montant']+$totalinterR['Montant'];
            $totalR['TVA']=      $totalnationR['TVA']+$totalinterR['TVA'];
            $recup_infosagences['sragence']= $totalR;

            $totalO['Nbr']=      $totalnationO['Nbr']+$totalinterO['Nbr'];
            $totalO['Poids']=    $totalnationO['Poids']+$totalinterO['Poids'];
            $totalO['Montant']=  $totalnationO['Montant']+$totalinterO['Montant'];
            $totalO['TVA']=      $totalnationO['TVA']+$totalinterO['TVA'];
            $recup_infosagences['soagence']= $totalO;


            $totalRO['Nbr']=      $totalR['Nbr']+$totalO['Nbr'];
            $totalRO['Poids']=    $totalR['Poids']+$totalO['Poids'];
            $totalRO['Montant']=  $totalR['Montant']+$totalO['Montant'];
            $totalRO['TVA']=      $totalR['TVA']+$totalO['TVA'];
            $recup_infosagences['sroagence']= $totalRO;


            $totalRG['Nbr']=      $totalRNG['Nbr']+$totalRIG['Nbr'];
            $totalRG['Poids']=    $totalRNG['Poids']+$totalRIG['Poids'];
            $totalRG['Montant']=  $totalRNG['Montant']+$totalRIG['Montant'];
            $totalRG['TVA']=      $totalRNG['TVA']+$totalRIG['TVA'];

            $totalOG['Nbr']=      $totalONG['Nbr']+$totalOIG['Nbr'];
            $totalOG['Poids']=    $totalONG['Poids']+$totalOIG['Poids'];
            $totalOG['Montant']=  $totalONG['Montant']+$totalOIG['Montant'];
            $totalOG['TVA']=      $totalONG['TVA']+$totalOIG['TVA'];

            $totalROG['Nbr']=      $totalRG['Nbr']+$totalOG['Nbr'];
            $totalROG['Poids']=    $totalRG['Poids']+$totalOG['Poids'];
            $totalROG['Montant']=  $totalRG['Montant']+$totalOG['Montant'];
            $totalROG['TVA']=      $totalRG['TVA']+$totalOG['TVA'];





            $agences[$i]=$recup_infosagences;
        }
//var_dump($agences);die();


        return $this->render('default/recapdepotshistorique.html.twig', array(
            
            'indexmois' => $indexmois,
            'mois' => $mois,
            'agences' => $agences,
            'totalRNG' => $totalRNG,
            'totalONG' => $totalONG,
            'totalRIG' => $totalRIG,
            'totalOIG' => $totalOIG,
            'totalNG' => $totalNG,
            'totalIG' => $totalIG,
            'totalRG' => $totalRG,
            'totalOG' => $totalOG,
            'totalROG' => $totalROG,
        ));
    }

    /**
     * @Route("/depots/journaliers", name="depots_journaliers")
     */
    public function depotsjournaliersAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $datepicker = $request->get('datepicker');
        $agence = $request->get('agence');
        //Date du jour
        if($datepicker==null){
            $date=date('d/m/Y');
        }else{
            $date=$datepicker;
        }

        if($agence=='ems'){
            $agencesAG = $em->getRepository('AppBundle:Agence')->findByAG();
        }elseif($agence=='pdk'){

            $agencesAG = $em->getRepository('AppBundle:Agence')->findByPD();
        }elseif($agence=='prg'){
            $agencesAG = $em->getRepository('AppBundle:Agence')->findByPR();
        }else{
            return $this->redirectToRoute('homepage');
        }


        //Liste des agences EMS

            //var_dump($agencesAG);die();

        $infosagences=array();
        $recup_infosagences=array();
        //Récupération des données
        $totalRNG=array('Nbr' => '0','Poids' => '0','Montant' => '0','TVA' => '0');
        $totalONG=array('Nbr' => '0','Poids' => '0','Montant' => '0','TVA' => '0');

        $totalRIG=array('Nbr' => '0','Poids' => '0','Montant' => '0','TVA' => '0');
        $totalOIG=array('Nbr' => '0','Poids' => '0','Montant' => '0','TVA' => '0');

        $totalNG=array('Nbr' => '0','Poids' => '0','Montant' => '0','TVA' => '0');
        $totalIG=array('Nbr' => '0','Poids' => '0','Montant' => '0','TVA' => '0');

        $totalRG=array('Nbr' => '0','Poids' => '0','Montant' => '0','TVA' => '0');
        $totalOG=array('Nbr' => '0','Poids' => '0','Montant' => '0','TVA' => '0');

        $totalROG=array('Nbr' => '0','Poids' => '0','Montant' => '0','TVA' => '0');

        for ($i=0; $i<count($agencesAG); $i++){
            $recup_infosagences['agence']= $agencesAG[$i];
            $id_agence= $agencesAG[$i]->getId();

            $totalR=array('Nbr' => '0','Poids' => '0','Montant' => '0','TVA' => '0');
            $totalO=array('Nbr' => '0','Poids' => '0','Montant' => '0','TVA' => '0');
            $totalRO=array('Nbr' => '0','Poids' => '0','Montant' => '0','TVA' => '0');
            //Situation nationale clients reguliers et occasionnels
            $echelle='National';
            $reg='Abonné';
            $occ='Occasionnel';
            $totalnationR=array('Nbr' => '0','Poids' => '0','Montant' => '0','TVA' => '0');
            $totalnationO=array('Nbr' => '0','Poids' => '0','Montant' => '0','TVA' => '0');
            $totalnationRO=array('Nbr' => '0','Poids' => '0','Montant' => '0','TVA' => '0');
            $depotsreg = $em->getRepository('AppBundle:Envoi')->findByAgence($id_agence, $reg, $date, $echelle);
            $depotsocc = $em->getRepository('AppBundle:Envoi')->findByAgence($id_agence, $occ, $date, $echelle);

            for($j=0; $j<count($depotsreg); $j++){
                $totalnationR['Nbr']=  $totalnationR['Nbr']+1;
                $totalnationR['Poids']=  $totalnationR['Poids']+ $depotsreg[$j]->getPoids();
                $totalnationR['Montant']=  $totalnationR['Montant']+ $depotsreg[$j]->getTarif();
                $totalnationR['TVA']=  $totalnationR['TVA']+ $depotsreg[$j]->getTva();
            }
            $recup_infosagences['snragence']= $totalnationR;

            $totalRNG['Nbr']=  $totalRNG['Nbr']+$totalnationR['Nbr'];
            $totalRNG['Poids']= $totalRNG['Poids']+ $totalnationR['Poids'];
            $totalRNG['Montant']=  $totalRNG['Montant']+$totalnationR['Montant'];
            $totalRNG['TVA']=  $totalRNG['TVA']+$totalnationR['TVA'];

            for($j=0; $j<count($depotsocc); $j++){
                $totalnationO['Nbr']=  $totalnationO['Nbr']+1;
                $totalnationO['Poids']=  $totalnationO['Poids']+ $depotsocc[$j]->getPoids();
                $totalnationO['Montant']=  $totalnationO['Montant']+ $depotsocc[$j]->getTarif();
                $totalnationO['TVA']=  $totalnationO['TVA']+ $depotsocc[$j]->getTva();
            }
            $recup_infosagences['snoagence']= $totalnationO;

            $totalONG['Nbr']=  $totalONG['Nbr']+$totalnationO['Nbr'];
            $totalONG['Poids']= $totalONG['Poids']+ $totalnationO['Poids'];
            $totalONG['Montant']=  $totalONG['Montant']+$totalnationO['Montant'];
            $totalONG['TVA']=  $totalONG['TVA']+$totalnationO['TVA'];


            $totalnationRO['Nbr']=      $totalnationR['Nbr']+$totalnationO['Nbr'];
            $totalnationRO['Poids']=    $totalnationR['Poids']+$totalnationO['Poids'];
            $totalnationRO['Montant']=  $totalnationR['Montant']+$totalnationO['Montant'];
            $totalnationRO['TVA']=      $totalnationR['TVA']+$totalnationO['TVA'];
            $recup_infosagences['snroagence']= $totalnationRO;


            $totalNG['Nbr']=      $totalRNG['Nbr']+$totalONG['Nbr'];
            $totalNG['Poids']=    $totalRNG['Poids']+$totalONG['Poids'];
            $totalNG['Montant']=  $totalRNG['Montant']+$totalONG['Montant'];
            $totalNG['TVA']=      $totalRNG['TVA']+$totalONG['TVA'];



            //Situation internationale clients reguliers et occasionnels
            $echelle='International';
            $reg='Abonné';
            $occ='Occasionnel';
            $totalinterR=array('Nbr' => '0','Poids' => '0','Montant' => '0','TVA' => '0');
            $totalinterO=array('Nbr' => '0','Poids' => '0','Montant' => '0','TVA' => '0');
            $totalinterRO=array('Nbr' => '0','Poids' => '0','Montant' => '0','TVA' => '0');
            $depotsreg = $em->getRepository('AppBundle:Envoi')->findByAgence($id_agence, $reg, $date, $echelle);
            $depotsocc = $em->getRepository('AppBundle:Envoi')->findByAgence($id_agence, $occ, $date, $echelle);

            for($j=0; $j<count($depotsreg); $j++){
                $totalinterR['Nbr']=  $totalinterR['Nbr']+1;
                $totalinterR['Poids']=  $totalinterR['Poids']+ $depotsreg[$j]->getPoids();
                $totalinterR['Montant']=  $totalinterR['Montant']+ $depotsreg[$j]->getTarif();
                $totalinterR['TVA']=  $totalinterR['TVA']+ $depotsreg[$j]->getTva();
            }
            $recup_infosagences['siragence']= $totalinterR;

            $totalRIG['Nbr']=  $totalRIG['Nbr']+$totalinterR['Nbr'];
            $totalRIG['Poids']= $totalRIG['Poids']+ $totalinterR['Poids'];
            $totalRIG['Montant']=  $totalRIG['Montant']+$totalinterR['Montant'];
            $totalRIG['TVA']=  $totalRIG['TVA']+$totalinterR['TVA'];


            for($j=0; $j<count($depotsocc); $j++){
                $totalinterO['Nbr']=  $totalinterO['Nbr']+1;
                $totalinterO['Poids']=  $totalinterO['Poids']+ $depotsocc[$j]->getPoids();
                $totalinterO['Montant']=  $totalinterO['Montant']+ $depotsocc[$j]->getTarif();
                $totalinterO['TVA']=  $totalinterO['TVA']+ $depotsocc[$j]->getTva();
            }
            $recup_infosagences['sioagence']= $totalinterO;


            $totalOIG['Nbr']=  $totalOIG['Nbr']+$totalinterO['Nbr'];
            $totalOIG['Poids']= $totalOIG['Poids']+ $totalinterO['Poids'];
            $totalOIG['Montant']=  $totalOIG['Montant']+$totalinterO['Montant'];
            $totalOIG['TVA']=  $totalOIG['TVA']+$totalinterO['TVA'];


            $totalIG['Nbr']=      $totalRIG['Nbr']+$totalOIG['Nbr'];
            $totalIG['Poids']=    $totalRIG['Poids']+$totalOIG['Poids'];
            $totalIG['Montant']=  $totalRIG['Montant']+$totalOIG['Montant'];
            $totalIG['TVA']=      $totalRIG['TVA']+$totalOIG['TVA'];

            $totalinterRO['Nbr']=      $totalinterR['Nbr']+$totalinterO['Nbr'];
            $totalinterRO['Poids']=    $totalinterR['Poids']+$totalinterO['Poids'];
            $totalinterRO['Montant']=  $totalinterR['Montant']+$totalinterO['Montant'];
            $totalinterRO['TVA']=      $totalinterR['TVA']+$totalinterO['TVA'];
            $recup_infosagences['siroagence']= $totalinterRO;


            $totalR['Nbr']=      $totalnationR['Nbr']+$totalinterR['Nbr'];
            $totalR['Poids']=    $totalnationR['Poids']+$totalinterR['Poids'];
            $totalR['Montant']=  $totalnationR['Montant']+$totalinterR['Montant'];
            $totalR['TVA']=      $totalnationR['TVA']+$totalinterR['TVA'];
            $recup_infosagences['sragence']= $totalR;

            $totalO['Nbr']=      $totalnationO['Nbr']+$totalinterO['Nbr'];
            $totalO['Poids']=    $totalnationO['Poids']+$totalinterO['Poids'];
            $totalO['Montant']=  $totalnationO['Montant']+$totalinterO['Montant'];
            $totalO['TVA']=      $totalnationO['TVA']+$totalinterO['TVA'];
            $recup_infosagences['soagence']= $totalO;


            $totalRO['Nbr']=      $totalR['Nbr']+$totalO['Nbr'];
            $totalRO['Poids']=    $totalR['Poids']+$totalO['Poids'];
            $totalRO['Montant']=  $totalR['Montant']+$totalO['Montant'];
            $totalRO['TVA']=      $totalR['TVA']+$totalO['TVA'];
            $recup_infosagences['sroagence']= $totalRO;


            $totalRG['Nbr']=      $totalRNG['Nbr']+$totalRIG['Nbr'];
            $totalRG['Poids']=    $totalRNG['Poids']+$totalRIG['Poids'];
            $totalRG['Montant']=  $totalRNG['Montant']+$totalRIG['Montant'];
            $totalRG['TVA']=      $totalRNG['TVA']+$totalRIG['TVA'];

            $totalOG['Nbr']=      $totalONG['Nbr']+$totalOIG['Nbr'];
            $totalOG['Poids']=    $totalONG['Poids']+$totalOIG['Poids'];
            $totalOG['Montant']=  $totalONG['Montant']+$totalOIG['Montant'];
            $totalOG['TVA']=      $totalONG['TVA']+$totalOIG['TVA'];

            $totalROG['Nbr']=      $totalRG['Nbr']+$totalOG['Nbr'];
            $totalROG['Poids']=    $totalRG['Poids']+$totalOG['Poids'];
            $totalROG['Montant']=  $totalRG['Montant']+$totalOG['Montant'];
            $totalROG['TVA']=      $totalRG['TVA']+$totalOG['TVA'];













            $agences[$i]=$recup_infosagences;
        }

        return $this->render('default/depotsjournaliers.html.twig', array(
            'journee' => $date,
            'agence'  => $agence,
            'agences' => $agences,
            'totalRNG' => $totalRNG,
            'totalONG' => $totalONG,
            'totalRIG' => $totalRIG,
            'totalOIG' => $totalOIG,
            'totalNG' => $totalNG,
            'totalIG' => $totalIG,
            'totalRG' => $totalRG,
            'totalOG' => $totalOG,
            'totalROG' => $totalROG,

        ));
    }


    /**
     * @Route("/facture/export", name="facture_export")
     */
    public function exportAction(Request $request)
    {

        $em = $this->getDoctrine()->getManager();
        //param facture
        $mois=$request->get('date');
        $reqmois='%'.$mois;
        $abonne = $request->get('abonne');



        //var_dump($mois);die();
        $tababonne=$em->getRepository('AppBundle:Abonne')->findOneById($abonne);
        $nomabonne=$tababonne[0]->getNom();
        $adresseabonne=$tababonne[0]->getAdresse();
        $id_abonne=$tababonne[0]->getId();
        $facture = $em->getRepository('AppBundle:Facture')->findOneByIdAbonnePeriode($id_abonne, $mois)[0];


        // var_dump($nomabonne);die();

        $envois = $em->getRepository('AppBundle:AbonneEnvoi')->findByIdAbonneMois($abonne, $reqmois);

        $envoisna = $em->getRepository('AppBundle:AbonneEnvoi')->findByIdAbonneMoisEchelle($abonne, $reqmois, 'National');
        $envoisin = $em->getRepository('AppBundle:AbonneEnvoi')->findByIdAbonneMoisEchelle($abonne, $reqmois, 'International');



        $tab=array();
        $recuptab=array();
        $totalna=0;
        $totalni=0;
        $total=0;
        $tva=0;

        if(count($envoisin)>=1){
            $recuptab['nature']='IN';
            $recuptab['date']='';
            $recuptab['agence']='';
            $recuptab['code']='';
            $recuptab['destination']='';
            $recuptab['poids']='';
            $recuptab['montant']='';

            $tab[count($tab)]=$recuptab;

            for($i=0; $i<count($envoisin); $i++){
                $recuptab['nature']='EN';
                $recuptab['date']=$envoisin[$i]->getEnvoi()->getDate();
                $recuptab['agence']=$envoisin[$i]->getEnvoi()->getAgence()->getNom();
                $recuptab['code']=$envoisin[$i]->getEnvoi()->getCodeenvoi();
                $recuptab['destination']=$envoisin[$i]->getEnvoi()->getDestinataire()->getUsager()->getPays()->getName();
                $recuptab['poids']=$envoisin[$i]->getEnvoi()->getPoids();
                $recuptab['montant']=$envoisin[$i]->getEnvoi()->getTarif();
                $totalni=$totalni+$envoisin[$i]->getEnvoi()->getTarif();

                $tva=$tva+$envoisin[$i]->getEnvoi()->getTva();

                $tab[count($tab)]=$recuptab;
                //var_dump($envoisin[$i]->getEnvoi()->getDestinataire()->getUsager()->getPays()->getName());die();

            }

            $recuptab['nature']='TNI';
            $recuptab['date']='';
            $recuptab['agence']='';
            $recuptab['code']='';
            $recuptab['destination']='';
            $recuptab['poids']='';
            $recuptab['montant']=$totalni;

            $tab[count($tab)]=$recuptab;


        }



        if(count($envoisna)>=1){
            $recuptab['nature']='NA';
            $recuptab['date']='';
            $recuptab['agence']='';
            $recuptab['code']='';
            $recuptab['destination']='';
            $recuptab['poids']='';
            $recuptab['montant']='';

            $tab[count($tab)]=$recuptab;

            for($i=0; $i<count($envoisna); $i++){
                $recuptab['nature']='EN';
                $recuptab['date']=$envoisna[$i]->getEnvoi()->getDate();
                $recuptab['agence']=$envoisna[$i]->getEnvoi()->getAgence()->getNom();
                $recuptab['code']=$envoisna[$i]->getEnvoi()->getCodeenvoi();
                $recuptab['destination']=$envoisna[$i]->getEnvoi()->getDestinataire()->getUsager()->getVille();
                $recuptab['poids']=$envoisna[$i]->getEnvoi()->getPoids();
                $recuptab['montant']=$envoisna[$i]->getEnvoi()->getTarif();
                $totalna=$totalna+$envoisna[$i]->getEnvoi()->getTarif();
                $tva=$tva+$envoisna[$i]->getEnvoi()->getTva();
                $tab[count($tab)]=$recuptab;
                //var_dump($envoisin[$i]->getEnvoi()->getDestinataire()->getUsager()->getPays()->getName());die();

            }

            $recuptab['nature']='TNA';
            $recuptab['date']='';
            $recuptab['agence']='';
            $recuptab['code']='';
            $recuptab['destination']='';
            $recuptab['poids']='';
            $recuptab['montant']=$totalna;

            $tab[count($tab)]=$recuptab;


        }


        $recuptab['nature']='TT';
        $recuptab['date']='';
        $recuptab['agence']='';
        $recuptab['code']='';
        $recuptab['destination']='';
        $recuptab['poids']='';
        $recuptab['montant']=$totalna+$totalni;

        $tab[count($tab)]=$recuptab;

        $recuptab['nature']='TVA';
        $recuptab['date']='';
        $recuptab['agence']='';
        $recuptab['code']='';
        $recuptab['destination']='';
        $recuptab['poids']='';
        $recuptab['montant']=$tva;

        $tab[count($tab)]=$recuptab;


        $recuptab['nature']='TTC';
        $recuptab['date']='';
        $recuptab['agence']='';
        $recuptab['code']='';
        $recuptab['destination']='';
        $recuptab['poids']='';
        $recuptab['montant']=$tva+$totalna+$totalni;

        $tab[count($tab)]=$recuptab;

        //var_dump($tab);die();

        $adresseabonne = str_replace(","," ",$adresseabonne);

        $writer = $this->container->get('egyg33k.csv.writer');
        $csv = $writer::createFromFileObject(new \SplTempFileObject());
        $csv->insertOne(['', '', '', 'Periode facturee :'.$mois, '', '']);

        $csv->insertOne(['', '', '', '', '', '']);

        $csv->insertOne([$nomabonne, '', '', '', '', '']);
        $csv->insertOne([$adresseabonne, '', '', '', '', '']);
        $csv->insertOne(['', '', '', '', '', '']);

        $csv->insertOne(['Facture : '. $facture->getNumfacture()."/". $facture->getPeriode() , '', '', 'Date edition : '. $facture->getDateedition(), '', '']);

        $csv->insertOne(['', '', '', '', '', '']);
        $csv->insertOne(['', '', '', '', '', '']);



        for($i=0; $i<count($tab); $i++){

            if($tab[$i]['nature' ]=='IN') {
                $csv->insertOne(['INTERNATIONAL', '', '', '', '', '']);
                $csv->insertOne(['Date', 'Lieu de depot', 'N depot', 'Destination ', 'Poids/Kg', 'Montant']);
            }
            elseif($tab[$i]['nature' ]=='NA'){
                $csv->insertOne(['NATIONAL', '', '', '', '', '']);
                $csv->insertOne(['Date', 'Lieu de depot', 'N depot', 'Destination ', 'Poids/Kg', 'Montant']);
            }
            elseif($tab[$i]['nature' ]=='TNA'){
                $csv->insertOne(['', '', '', 'Total', '', $tab[$i]['montant']]);
            }
            elseif($tab[$i]['nature' ]=='TNI'){
                $csv->insertOne(['', '', '', 'Total', '', $tab[$i]['montant']]);
            }
            elseif($tab[$i]['nature' ]=='TT'){
                $csv->insertOne(['', '', '', 'Montant HT', '', $tab[$i]['montant']]);
            }
            elseif($tab[$i]['nature' ]=='TVA'){
                $csv->insertOne(['', '', '', 'TVA', '', $tab[$i]['montant']]);
            }
            elseif($tab[$i]['nature' ]=='TTC'){
                $csv->insertOne(['', '', '', 'Total TTC', '', $tab[$i]['montant']]);
                $csv->insertOne(['', '', '', '', '', '']);
                $csv->insertOne([' Arretee la presente facture a la somme de : ', '', '', $tab[$i]['montant'], '', '']);
                $csv->insertOne(['   '.$facture->getArret(), '', '', '', '', '']);
                $csv->insertOne(['CONDITIONS DE PAIEMENT :', '', '', '', '', '']);
                $csv->insertOne([' DATE LIMITE DE PAIEMENT :  20 jours apres reception de la facture', '', '', '', '', '']);

                $vrm= str_replace(","," ",$facture->getVirement());
                $csv->insertOne(['VIREMENT : -'.$vrm, '', '', '', '', '']);

                $csv->insertOne(['', '', '', '', '', '']);
                $csv->insertOne([' Cheque a libeller au nom de EMS SENEGAL', '', '', '', '', '']);
                $csv->insertOne(['', '', '', '', '', '']);

                $csv->insertOne(['LE DIRECTEUR DE L ADMINISTRATION', '', '', 'LE DIRECTEUR GENERAL', '', '']);
                $csv->insertOne(['DES FINANCES ET DE LA COMPTABILITE', '', '', '', '', '']);
                $csv->insertOne(['', '', '', '', '', '']);
                $csv->insertOne(['', '', '', '', '', '']);
                $csv->insertOne(['', '', '', '', '', '']);
                $csv->insertOne(['    IBRAHIMA DIONE', '', '', '    SERIGNE GUEYE', '', '']);

            }
            else{
                $csv->insertOne([$tab[$i]['date'], $tab[$i]['agence'], $tab[$i]['code'], $tab[$i]['destination'], $tab[$i]['poids'], $tab[$i]['montant']]);
            }


        }

        $perfact= str_replace("/","",$facture->getPeriode());
        $csv->output('facture_du_'.$perfact.'.csv');


       exit;
    }


    
    /**
     * @Route("/taxe/data/json", name="taxe_data_json")
     * @Method({"GET", "POST"})
     */
    public function printAction(Request $request)
    {
        //var_dump($request->request->all());
         $em = $this->getDoctrine()->getManager();
        //Recuperation de la zone de taxation
         $id_paysdes = $request->get('pays');
         
         $poids = $request->get('poids');
         $typeenvoi = $request->get('typeenvoi');
         
         //Me donne info si na ou int , Tout Type ; Abonne ou Occasionnel
         $natureclient = $request->get('client');
         
         
         //Determination de libelle taxation du client
         $nomclient = $request->get('code_abonne');
         $abonnetab = $em->getRepository('AppBundle:Abonne')->findOneByNom($nomclient);
         $libtaxe = 'rien';
//         if($abonnetab){
//            $libtaxe = $abonnetab[0]->getType();
//         }
         
      

       return new JsonResponse([
       'poids'=>$libtaxe
       ]);


    }
    
    /**
     * @Route("/track/trace", name="track_trace")
     */
    public function tracktraceAction(Request $request)
    {
       
      

        return $this->render('default/tracktrace.html.twig', array(
            

        ));

    }
    
    
    /**
     * @Route("/tester/existence/abonne", name="tester_existence_abonne")
     * @Method({"GET", "POST"})
     */
    public function testerexistenceabonneAction(Request $request)
    {
        //var_dump($request->request->all());
         $em = $this->getDoctrine()->getManager();
         //Recuperation du libelle client
         $nomclient = '2 IRIS';//$request->get('client');
         $abonne = $em->getRepository('AppBundle:Abonne')->findOneByNom($nomclient);
          $abonnes=$em->getRepository('AppBundle:Abonne')->findByAbonnes();
        
          $tababonnes=array();
           for ($i=0; $i<count($abonnes); $i++){
               $tab['nom']=$abonnes[$i]->getNom();
                $tababonnes[$i]= $tab['nom'];
           }
             //var_dump($tababonnes);
         $info=false;
         if($abonne){
            $info = true;
         }
         
      

       return new JsonResponse($tababonnes);


    }


    /**
     * @param $id_abonne
     * @param $periode
     *
     * @return string
     */
    public function isRecorved($id_abonne, $periode, $recouvree){
        $em = $this->getDoctrine()->getManager();
        $facture = $em->getRepository('AppBundle:Facture')->findOneByIdAbonnePeriodeEtat($id_abonne, $periode, $recouvree);

        if($facture){
            return 'OUI';
        }else{
            return 'NON';
        }

    }




    /**
    Calcules de dates.
    Ecrite par Dominique FERET le 17 Juin 2014

    fonction paques(annee) => renvoi la date du dimanche de paques basé sur l'algorythme de Gauss.
    fonction ferie(annee) => renvoi un tableau de tout les jours férié de l'année
    fonction trouvejourouvre(date,decalage) => renvoi le prochain jour ouvré dans le sens du décalage (exprimé en jours)

    exemple d'utilisation
    echo trouvejourouvre("02-01-2014",-1)."<br>";
    => renverra 31-12-2013

    echo trouvejourouvre("15-08-2014",1)."<br>";
    => renverra 18-08-2018 => le prochain jour ouvré après le 15 aout 2014 sera le 18 Aout.

    echo trouvejourouvre("17-07-2014",-3)."<br>";
    => renverra 11-07-2014 => le jour ouvré j-3 du 17 juillet sera le 11 car j-3 = 14 (le 12 et 13 étant samedi dimanche)
     */


   public function paques($annee){
        $a=$annee%19;
        $b=$annee%4;
        $c=$annee%7;
        $d=(19*$a+24)%30;
        $e=(2*$b+4*$c+6*$d+5)%7;
        $j=22+$d+$e;
        $m=3;
        if($j>31){
            $m+=1;
            $j-=31;
        }
        $datepaques=sprintf("%02d-%02d-%04d",$j,$m,$annee);
        return $datepaques;
    }



    public function ferie($annee){
        $listedate=array();
        $listedate[]=date("01-01-".$annee);
        $listedate[]=date("01-05-".$annee);
        $listedate[]=date("08-05-".$annee);
        $listedate[]=date("14-07-".$annee);
        $listedate[]=date("15-08-".$annee);
        $listedate[]=date("01-11-".$annee);
        $listedate[]=date("11-11-".$annee);
        $listedate[]=date("25-12-".$annee);
        $datepaques=strtotime($this->paques($annee));

        $listedate[]=date('d-m-Y',strtotime('+1 day',$datepaques));
        $listedate[]=date('d-m-Y',strtotime('+39 days',$datepaques));
        $listedate[]=date('d-m-Y',strtotime('+50 days',$datepaques));
        return $listedate;
    }

    // cette fonction permet de trouver le jour ouvré correspondant a une date + ou - un nombre de jour

    public function trouvejourouvre($dateactuelle,$decalage)
    {
        $datecalculee=strtotime($dateactuelle);
        $datecalculee+=($decalage*86400);
        // a ce stade, $datecalcule contient la date demandée sans tenir compte des jours ouvrés.

        //le décalage ensuite se fera jour par jour en plus ou en moins selon le décalage initiale
        $decalage=($decalage>0)?86400:-86400;
        //boucle
        $x=0;
        do {
            $x++;
            // Si le jour suivant n'est ni un dimanche (0) ou un samedi (6), ni un jour férié, on sort, sinon on ajoute ou on retire un jour
            if (!in_array(date('w', $datecalculee), array(0, 6)) && !in_array(date('d-m-Y',$datecalculee), $this->ferie(date("Y",$datecalculee)))) {
                break;

            } else {
                $datecalculee+=$decalage;
            }
        }  while ($x<10); // petite sécurité,certes inutile mais je déteste les boucles infinies
        return( date('d-m-Y',$datecalculee));

    }





}
