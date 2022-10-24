<?php

namespace AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class PrintAllController extends Controller
{
    /**
     * @Route("/printall", name="printall_pdf")
     */
    public function indexAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        //Recuperation du mois en parametre selon les actions de click trois cas


        //1 cas
        $date=date('d/m/Y');
        $date='%'.substr($date, 2);

        //2 cas
        $getmois = $request->get('mois');
        $indexannee = $request->get('annee');
        if($getmois!=null){
            $date='%/'.$getmois.'/'.$indexannee;
        }

        //3 cas
        $periode = $request->get('periode');
        if($periode!=null){
            $date='%/'.$periode;
        }


        $mois= $request->get('date');
        $reqmois='%/'.$mois;
        //Détermination dernier jour ouvrable du mois

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

        //var_dump($mois);die();

        //Determination des clients abonnes ayant fait au moins une emission dans le mois
        $abonnes = $em->getRepository('AppBundle:Facture')->findByPeriodeAbonnes($mois);
        //var_dump($abonnes);die();
        $pdf = new \FPDF();
       // $nbbq=0;
        for($l=0; $l<count($abonnes); $l++){

           // $nbbq=$nbbq+1;

            $pdf->AddPage();
            $PageNo=0;
            //Header page
            $pdf->Image("img/logo-ems.jpg",1,null,50,12);
            $pdf->Ln(5);
            $nbrp=0;
            //Body page
            $pdf->SetFont('Helvetica','', 11);

            $id_abonne= $abonnes[$l]->getAbonne()->getId();
            $tababonne=$em->getRepository('AppBundle:Abonne')->findOneById($id_abonne);
            $nomabonne=$tababonne[0]->getNom();
            $adresseabonne=$tababonne[0]->getAdresse();

            $facture = $em->getRepository('AppBundle:Facture')->findOneByIdAbonnePeriode($id_abonne, $mois);

                $facture = $facture[0];


                $envoisna = $em->getRepository('AppBundle:AbonneEnvoi')->findByIdAbonneMoisEchelle($id_abonne, $reqmois, 'National');
                $envoisin = $em->getRepository('AppBundle:AbonneEnvoi')->findByIdAbonneMoisEchelle($id_abonne, $reqmois, 'International');

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


                $recuptab['nature']='PR';
                $recuptab['date']='';
                $recuptab['id']='';
                $recuptab['agence']='';
                $recuptab['code']=utf8_decode($moislettre);
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


                    $recuptab['nature']='INE';
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

                    $recuptab['nature']='NAE';
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





                $tab[count($tab)]=$recuptab;

                $reperes=count($tab);
                //var_dump(count($tab));die();

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


                if($reperes!=20 and  $reperes!=21){
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




                /** $modulo=count($tab)%34;
                //var_dump($mod);die();
                if($modulo>28){
                for($i=$modulo; $i<=34; $i++)
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
                }**/


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




                if( $reperes!=21){
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


                $mod=0;
                // var_dump($mod);die();


                $nbpage=1;

                if($reperes<=21){
                    $nbpage=1;
                    $mod=19-count($tab);
                }elseif($reperes>21 and $reperes<=33){
                    $nbpage= 2;

                    $mod=34-count($tab)%34;
                }else{
                    $nbpage= (count($tab)-count($tab)%34)/34;

                    $mod=34-count($tab)%34;
                }




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





                if($reperes<=21){



                    for($i=0; $i<count($tab); $i++){

                        if($i!=0 and $i%34==0 and $nbpage>19){
                            // $nbrp=$nbrp+1;
                            $PageNo= $PageNo+1;
                            //Footer page
                            $pdf->Ln(6);
                            $pdf->SetFont('Helvetica','I',9);
                            $infopieds1=utf8_decode('Domaine SODIDA, lot n° 49 Dakar Tél. (221) 869.01.01 / Fax : 869.01.02 BP: 15 305 Email : ems@ems.sn');
                            $infopieds2='RC SN DK 2008 M19490-NINEA 002591536 2G3';
                            //$infopieds=$infopieds1."\n".$infopieds2;
                            $pdf->Cell(163,6,$infopieds1,'TR',0,'C');
                            $pdf->SetFont('Helvetica','B',9);
                            $pdf->Cell(28,6,'Page '.  $PageNo." sur ".$nbpage,0,0,'C');
                            $pdf->Ln(4);
                            $pdf->SetFont('Helvetica','I',9);
                            $pdf->Cell(163,6,$infopieds2,'R',0,'C');
                            $pdf->Cell(28,6,'',0,0,'C');

                            //New page
                            $pdf->AddPage();
                            $pdf->SetFont('Helvetica','', 11);
                            //Header page
                            $pdf->Image("img/logo-ems.jpg",1,null,50,12);
                            $pdf->Ln(5);
                        }



                        if($tab[$i]['nature']=='PR'){
                            $pdf->Cell(103,6,'',0,0,'C');
                            $tetx='Période facturée :';
                            $pdf->SetFont('Helvetica','B', 11);
                            $pdf->Cell(58,6,utf8_decode($tetx),0,0,'R');

                            $pdf->SetFont('Helvetica','', 11);
                            $pdf->Cell(30,6,$tab[$i]['code'],0,0,'L');
                            $pdf->Ln(6);
                        }elseif($tab[$i]['nature']=='NAB'){

                            $pdf->SetFont('Helvetica','B', 11);
                            $str= utf8_encode($this->removeAccents($tab[$i]['code']));
                            $pdf->MultiCell(103,6,strtoupper($str),0,'L');

                            $pdf->SetFont('Helvetica','', 11);
                            $pdf->MultiCell(88,6,'',0);
                        }elseif($tab[$i]['nature']=='AAB'){

                            $pdf->SetFont('Helvetica','B', 11);
                            //$pdf->Cell(103,6,utf8_decode($tab[$i]['code']),1,0,'L');

                            $str= utf8_encode($this->removeAccents($tab[$i]['code']));
                            $pdf->MultiCell(103,6,strtoupper($str),0,'L');

                            $pdf->SetFont('Helvetica','', 11);
                            //$pdf->Cell(88,6,'',0,0);
                            $pdf->MultiCell(88,6,'',0);
                        }elseif($tab[$i]['nature']=='LV'){


                            //$pdf->SetFont('Helvetica','', 11);
                            $pdf->Cell(191,6,'',0,0);
                            $pdf->Ln(6);
                        }elseif($tab[$i]['nature']=='NFA'){

                            $pdf->SetFont('Helvetica','B', 11);
                            $pdf->Cell(18,6,'Facture :',0,0,'L');

                            $pdf->SetFont('Helvetica','', 11);
                            $numfacture=$facture->getNumfacture();
                            $pdf->Cell(108,6,$numfacture.'/'.$mois,0,0,'L');

                            $pdf->SetFont('Helvetica','B', 11);
                            $pdf->Cell(30,6,utf8_decode("Date d'édition :"),0,0,'L');

                            $pdf->SetFont('Helvetica','', 11);
                            //$dateeditionfacture=$facture->getDateedition();
                            $pdf->Cell(30,6,$djomois,0,0);
                            //$pdf->MultiCell(88,4,'',0);
                            $pdf->Ln(6);
                        }elseif($tab[$i]['nature']=='IN'){
                            $pdf->Ln(6);
                            $pdf->SetFont('Helvetica','B', 11);
                            $pdf->Cell(103,6,'INTERNATIONAL',0,0,'L');

                            $pdf->SetFont('Helvetica','', 11);
                            $pdf->Cell(88,6,'',0,0);
                            $pdf->Ln(6);
                        }elseif($tab[$i]['nature']=='INE'){
                            $pdf->SetFont('Helvetica','B', 11);
                            $pdf->Cell(23,6,utf8_decode('Date'),1,0,'C');
                            $pdf->Cell(40,6,utf8_decode('Lieu de dépôt'),1,0,'C');
                            $pdf->Cell(40,6,utf8_decode('N° dépot'),1,0,'C');
                            $pdf->Cell(40,6,utf8_decode('Destination'),1,0,'C');
                            $pdf->Cell(20,6,utf8_decode('Poids/Kg'),1,0,'C');
                            $pdf->Cell(28,6,utf8_decode('Montant'),1,0,'C');
                            $pdf->SetFont('Helvetica','', 11);
                            $pdf->Ln(6);
                        }elseif($tab[$i]['nature']=='EN'){
                            $pdf->Cell(23,6,utf8_decode($tab[$i]['date']),1,0,'C');
                            $pdf->Cell(40,6,strtoupper(utf8_decode($tab[$i]['agence'])),1,0,'L');

                            $pdf->Cell(40,6,strtoupper(utf8_decode($tab[$i]['code'])),1,0,'C');

//                            if($tab[$i]['destfacture']!='etr'){
//                                $pdf->Cell(40,6,strtoupper(utf8_decode($tab[$i]['destfacture'])),1,0,'L');
//                            }else{
                                $pdf->Cell(40,6,strtoupper(utf8_decode($tab[$i]['destination'])),1,0,'L');
                           // }


                            $pdf->Cell(20,6,utf8_decode($tab[$i]['poids']),1,0,'C');

                            $pdf->Cell(28,6,utf8_decode(str_replace(","," ", number_format($tab[$i]['montant']))),1,0,'R');
                            $pdf->Ln(6);
                        }elseif($tab[$i]['nature']=='NA'){
                            $pdf->Ln(6);
                            $pdf->SetFont('Helvetica','B', 11);
                            $pdf->Cell(103,6,'NATIONAL',0,0,'L');

                            $pdf->SetFont('Helvetica','', 11);
                            $pdf->Cell(88,6,'',0,0);
                            $pdf->Ln(6);
                        }elseif($tab[$i]['nature']=='NAE'){
                            $pdf->SetFont('Helvetica','B', 11);
                            $pdf->Cell(23,6,utf8_decode('Date'),1,0,'C');
                            $pdf->Cell(40,6,utf8_decode('Lieu de dépôt'),1,0,'C');
                            $pdf->Cell(40,6,utf8_decode('N° dépot'),1,0,'C');
                            $pdf->Cell(40,6,utf8_decode('Destination'),1,0,'C');
                            $pdf->Cell(20,6,utf8_decode('Poids/Kg'),1,0,'C');
                            $pdf->Cell(28,6,utf8_decode('Montant'),1,0,'C');
                            $pdf->SetFont('Helvetica','', 11);
                            $pdf->Ln(6);
                        }elseif($tab[$i]['nature']=='TNI'){
                            $pdf->Cell(103,6,'',0,0,'C');

                            $pdf->SetFont('Helvetica','B', 11);
                            $pdf->Cell(60,6,'Total',1,0,'L');

                            $pdf->SetFont('Helvetica','', 11);
                            $pdf->Cell(28,6,utf8_decode(str_replace(","," ", number_format($tab[$i]['montant']))),1,0,'R');
                            $pdf->Ln(6);
                        }elseif($tab[$i]['nature']=='TNA'){
                            $pdf->Cell(103,6,'',0,0,'C');

                            $pdf->SetFont('Helvetica','B', 11);
                            $pdf->Cell(60,6,'Total',1,0,'L');

                            $pdf->SetFont('Helvetica','', 11);
                            $pdf->Cell(28,6,utf8_decode(str_replace(","," ", number_format($tab[$i]['montant']))),1,0,'R');
                            $pdf->Ln(6);
                        }elseif($tab[$i]['nature']=='TT'){
                            $pdf->Cell(103,6,'',0,0,'C');

                            $pdf->SetFont('Helvetica','B', 11);
                            $pdf->Cell(60,6,'Montant HT',1,0,'L');

                            $pdf->SetFont('Helvetica','', 11);
                            $pdf->Cell(28,6,utf8_decode(str_replace(","," ", number_format($tab[$i]['montant']))),1,0,'R');
                            $pdf->Ln(6);
                        }elseif($tab[$i]['nature']=='TVA'){
                            $pdf->Cell(103,6,'',0,0,'C');

                            $pdf->SetFont('Helvetica','B', 11);
                            $pdf->Cell(60,6,'TVA',1,0,'L');

                            $pdf->SetFont('Helvetica','', 11);
                            $pdf->Cell(28,6,utf8_decode(str_replace(","," ", number_format($tab[$i]['montant']))),1,0,'R');
                            $pdf->Ln(6);
                        }elseif($tab[$i]['nature']=='TTC'){
                            $pdf->Cell(103,6,'',0,0,'C');

                            $pdf->SetFont('Helvetica','B', 11);
                            $pdf->Cell(60,6,'Total TTC',1,0,'L');

                            $pdf->SetFont('Helvetica','', 11);
                            $pdf->Cell(28,6,utf8_decode(str_replace(","," ", number_format($tab[$i]['montant']))),1,0,'R');
                            $pdf->Ln(6);
                        }elseif($tab[$i]['nature']=='TAC'){


                            $pdf->SetFont('Helvetica','B', 11);
                            $pdf->Cell(111,6,utf8_decode('Arrétée la présente facture à la somme de :     '.str_replace(","," ", number_format($tab[$i]['montant'])).' F'),0,0,'L');

                            $pdf->SetFont('Helvetica','', 11);
                            $pdf->Cell(80,6,'',0,0,'L');



                            $pdf->Ln(6);
                        }elseif($tab[$i]['nature']=='TAL'){

                            $formatter = \NumberFormatter::create('fr_FR', \NumberFormatter::SPELLOUT);
                            $formatter->setAttribute(\NumberFormatter::FRACTION_DIGITS, 0);
                            $formatter->setAttribute(\NumberFormatter::ROUNDING_MODE, \NumberFormatter::ROUND_HALFUP);
                            $ttlettre= $formatter->format($tab[$i]['montant']);   // un million cinq cent vingt-deux mille cinq cent trente
                            $pdf->SetFont('Helvetica','B', 11);
                            $pdf->Cell(191,6,utf8_decode('     '.strtoupper($ttlettre).' FCFA'),0,0,'L');
                            $pdf->SetFont('Helvetica','', 11);
                            $pdf->Ln(6);
                        }elseif($tab[$i]['nature']=='CDT'){

                            $pdf->SetFont('Helvetica','U', 11);
                            $pdf->Cell(191,6,utf8_decode('CONDITIONS DE PAIEMENT :'),0,0,'L');
                            $pdf->SetFont('Helvetica','', 11);
                            $pdf->Ln(6);
                        }elseif($tab[$i]['nature']=='DLM'){


                            $pdf->SetFont('Helvetica','', 11);
                            $pdf->Cell(56,6,utf8_decode('DATE LIMITE DE PAIEMENT :  '),0,0,'L');

                            $pdf->SetFont('Helvetica','B', 9);
                            $pdf->Cell(135,6,utf8_decode('20 jours après réception de la facture'),0,0,'L');

                            $pdf->SetFont('Helvetica','', 11);

                            $pdf->Ln(6);
                        }elseif($tab[$i]['nature']=='VRM'){

                            $vrmfacture=$facture->getVirement();
                            $pdf->SetFont('Helvetica','', 11);
                            $pdf->Cell(24,6,utf8_decode('VIREMENT : '),0,0,'L');

                            $pdf->SetFont('Helvetica','B', 9);
                            $pdf->Cell(167,6,utf8_decode($vrmfacture),0,0,'L');

                            $pdf->SetFont('Helvetica','', 11);

                            $pdf->Ln(6);
                        }elseif($tab[$i]['nature']=='ACQ'){

                            $pdf->SetFont('Helvetica','', 9);
                            $pdf->Cell(191,6,utf8_decode('Chèque à libeller au nom de EMS SENEGAL'),0,0,'L');
                            $pdf->SetFont('Helvetica','', 11);
                            $pdf->Ln(6);
                        }elseif($tab[$i]['nature']=='LAU1'){


                            $pdf->SetFont('Helvetica','', 10);
                            $pdf->Cell(95,6,utf8_decode("LE DIRECTEUR DE L'ADMINISTRATION DES"),0,0,'L');

                            $pdf->SetFont('Helvetica','', 10);


                            if($autDG==null){
                                $pdf->Cell(96,6,'LE DIRECTEUR GENERAL',0,0,'L');
                            }else{

                                if($autDG->getTitre()=='DGTITULAIRE'){
                                    $pdf->Cell(96,6,'LE DIRECTEUR GENERAL',0,0,'L');
                                }else{
                                    $pdf->Cell(96,6,'LE DIRECTEUR GENERAL PAR INTERIM',0,0,'L');
                                }

                            }

                            $pdf->Ln(4);
                        }elseif($tab[$i]['nature']=='LAU2'){


                            $pdf->SetFont('Helvetica','', 10);
                            if($autDAFC==null){
                                $pdf->Cell(95,6,'LE DIRECTEUR GENERAL',0,0,'L');
                            }else{

                                if($autDAFC->getTitre()=='DAFCTITULAIRE'){
                                    $pdf->Cell(95,6,'FINANCES ET DE LA COMPTABILITE',0,0,'L');
                                }else{
                                    $pdf->Cell(95,6,'FINANCES ET DE LA COMPTABILITE PAR INTERIM',0,0,'L');
                                }

                            }

                            $pdf->SetFont('Helvetica','', 10);
                            $pdf->Cell(96,6,'',0,0,'L');

                            $pdf->Ln(6);
                        }elseif($tab[$i]['nature']=='LAU3'){


                            $pdf->SetFont('Helvetica','', 10);
                            if($autDAFC!=null){
                                $nomdaf=$autDAFC->getNom();
                                $pdf->Cell(95,6,utf8_decode(strtoupper($nomdaf)),0,0,'L');
                            }else{
                                $pdf->Cell(95,6,'',0,0,'L');
                            }

                            $pdf->SetFont('Helvetica','', 10);
                            if($autDG!=null){
                                $nomdg=$autDG->getNom();
                                $pdf->Cell(96,6,utf8_decode(strtoupper($nomdg)),0,0,'L');
                            }else{
                                $pdf->Cell(96,6,'',0,0,'L');
                            }

                            $pdf->Ln(6);
                        }



                    }



                    for($i=count($tab); $i<34; $i++){



                        //$pdf->SetFont('Helvetica','', 11);
                        $pdf->Cell(191,6,'',0,0);
                        $pdf->Ln(6);

                    }

//var_dump('En construction');die();


                }elseif( $reperes>21 and $reperes<=33){


                    for($i=0; $i<$reperes-4; $i++){


                        if($tab[$i]['nature']=='PR'){
                            $pdf->Cell(103,6,'',0,0,'C');
                            $tetx='Période facturée :';
                            $pdf->SetFont('Helvetica','B', 11);
                            $pdf->Cell(58,6,utf8_decode($tetx),0,0,'R');

                            $pdf->SetFont('Helvetica','', 11);
                            $pdf->Cell(30,6,$tab[$i]['code'],0,0,'L');
                            $pdf->Ln(6);
                        }elseif($tab[$i]['nature']=='NAB'){

                            $pdf->SetFont('Helvetica','B', 11);
                            $str= utf8_encode($this->removeAccents($tab[$i]['code']));
                            $pdf->MultiCell(103,6,strtoupper($str),0,'L');

                            $pdf->SetFont('Helvetica','', 11);
                            $pdf->MultiCell(88,6,'',0);
                        }elseif($tab[$i]['nature']=='AAB'){

                            $pdf->SetFont('Helvetica','B', 11);
                            //$pdf->Cell(103,6,utf8_decode($tab[$i]['code']),1,0,'L');

                            $str= utf8_encode($this->removeAccents($tab[$i]['code']));
                            $pdf->MultiCell(103,6,strtoupper($str),0,'L');

                            $pdf->SetFont('Helvetica','', 11);
                            //$pdf->Cell(88,6,'',0,0);
                            $pdf->MultiCell(88,6,'',0);
                        }elseif($tab[$i]['nature']=='LV'){


                            //$pdf->SetFont('Helvetica','', 11);
                            $pdf->Cell(191,6,'',0,0);
                            $pdf->Ln(6);
                        }elseif($tab[$i]['nature']=='NFA'){

                            $pdf->SetFont('Helvetica','B', 11);
                            $pdf->Cell(18,6,'Facture :',0,0,'L');

                            $pdf->SetFont('Helvetica','', 11);
                            $numfacture=$facture->getNumfacture();
                            $pdf->Cell(108,6,$numfacture.'/'.$mois,0,0,'L');

                            $pdf->SetFont('Helvetica','B', 11);
                            $pdf->Cell(30,6,utf8_decode("Date d'édition :"),0,0,'L');

                            $pdf->SetFont('Helvetica','', 11);
                            //$dateeditionfacture=$facture->getDateedition();
                            $pdf->Cell(30,6,$djomois,0,0);
                            //$pdf->MultiCell(88,4,'',0);
                            $pdf->Ln(6);
                        }elseif($tab[$i]['nature']=='IN'){
                            $pdf->Ln(6);
                            $pdf->SetFont('Helvetica','B', 11);
                            $pdf->Cell(103,6,'INTERNATIONAL',0,0,'L');

                            $pdf->SetFont('Helvetica','', 11);
                            $pdf->Cell(88,6,'',0,0);
                            $pdf->Ln(6);
                        }elseif($tab[$i]['nature']=='INE'){
                            $pdf->SetFont('Helvetica','B', 11);
                            $pdf->Cell(23,6,utf8_decode('Date'),1,0,'C');
                            $pdf->Cell(40,6,utf8_decode('Lieu de dépôt'),1,0,'C');
                            $pdf->Cell(40,6,utf8_decode('N° dépot'),1,0,'C');
                            $pdf->Cell(40,6,utf8_decode('Destination'),1,0,'C');
                            $pdf->Cell(20,6,utf8_decode('Poids/Kg'),1,0,'C');
                            $pdf->Cell(28,6,utf8_decode('Montant'),1,0,'C');
                            $pdf->SetFont('Helvetica','', 11);
                            $pdf->Ln(6);
                        }elseif($tab[$i]['nature']=='EN'){
                            $pdf->Cell(23,6,utf8_decode($tab[$i]['date']),1,0,'C');
                            $pdf->Cell(40,6,strtoupper(utf8_decode($tab[$i]['agence'])),1,0,'L');

                            $pdf->Cell(40,6,strtoupper(utf8_decode($tab[$i]['code'])),1,0,'C');

                            if($tab[$i]['destfacture']!='etr'){
                                $pdf->Cell(40,6,strtoupper(utf8_decode($tab[$i]['destfacture'])),1,0,'L');
                            }else{
                                $pdf->Cell(40,6,strtoupper(utf8_decode($tab[$i]['destination'])),1,0,'L');
                            }


                            $pdf->Cell(20,6,utf8_decode($tab[$i]['poids']),1,0,'C');

                            $pdf->Cell(28,6,utf8_decode(str_replace(","," ", number_format($tab[$i]['montant']))),1,0,'R');
                            $pdf->Ln(6);
                        }elseif($tab[$i]['nature']=='NA'){
                            $pdf->Ln(6);
                            $pdf->SetFont('Helvetica','B', 11);
                            $pdf->Cell(103,6,'NATIONAL',0,0,'L');

                            $pdf->SetFont('Helvetica','', 11);
                            $pdf->Cell(88,6,'',0,0);
                            $pdf->Ln(6);
                        }elseif($tab[$i]['nature']=='NAE'){
                            $pdf->SetFont('Helvetica','B', 11);
                            $pdf->Cell(23,6,utf8_decode('Date'),1,0,'C');
                            $pdf->Cell(40,6,utf8_decode('Lieu de dépôt'),1,0,'C');
                            $pdf->Cell(40,6,utf8_decode('N° dépot'),1,0,'C');
                            $pdf->Cell(40,6,utf8_decode('Destination'),1,0,'C');
                            $pdf->Cell(20,6,utf8_decode('Poids/Kg'),1,0,'C');
                            $pdf->Cell(28,6,utf8_decode('Montant'),1,0,'C');
                            $pdf->SetFont('Helvetica','', 11);
                            $pdf->Ln(6);
                        }elseif($tab[$i]['nature']=='TNI'){
                            $pdf->Cell(103,6,'',0,0,'C');

                            $pdf->SetFont('Helvetica','B', 11);
                            $pdf->Cell(60,6,'Total',1,0,'L');

                            $pdf->SetFont('Helvetica','', 11);
                            $pdf->Cell(28,6,utf8_decode(str_replace(","," ", number_format($tab[$i]['montant']))),1,0,'R');
                            $pdf->Ln(6);
                        }elseif($tab[$i]['nature']=='TNA'){
                            $pdf->Cell(103,6,'',0,0,'C');

                            $pdf->SetFont('Helvetica','B', 11);
                            $pdf->Cell(60,6,'Total',1,0,'L');

                            $pdf->SetFont('Helvetica','', 11);
                            $pdf->Cell(28,6,utf8_decode(str_replace(","," ", number_format($tab[$i]['montant']))),1,0,'R');
                            $pdf->Ln(6);
                        }elseif($tab[$i]['nature']=='TT'){
                            $pdf->Cell(103,6,'',0,0,'C');

                            $pdf->SetFont('Helvetica','B', 11);
                            $pdf->Cell(60,6,'Montant HT',1,0,'L');

                            $pdf->SetFont('Helvetica','', 11);
                            $pdf->Cell(28,6,utf8_decode(str_replace(","," ", number_format($tab[$i]['montant']))),1,0,'R');
                            $pdf->Ln(6);
                        }elseif($tab[$i]['nature']=='TVA'){
                            $pdf->Cell(103,6,'',0,0,'C');

                            $pdf->SetFont('Helvetica','B', 11);
                            $pdf->Cell(60,6,'TVA',1,0,'L');

                            $pdf->SetFont('Helvetica','', 11);
                            $pdf->Cell(28,6,utf8_decode(str_replace(","," ", number_format($tab[$i]['montant']))),1,0,'R');
                            $pdf->Ln(6);
                        }elseif($tab[$i]['nature']=='TTC'){
                            $pdf->Cell(103,6,'',0,0,'C');

                            $pdf->SetFont('Helvetica','B', 11);
                            $pdf->Cell(60,6,'Total TTC',1,0,'L');

                            $pdf->SetFont('Helvetica','', 11);
                            $pdf->Cell(28,6,utf8_decode(str_replace(","," ", number_format($tab[$i]['montant']))),1,0,'R');
                            $pdf->Ln(6);
                        }elseif($tab[$i]['nature']=='TAC'){


                            $pdf->SetFont('Helvetica','B', 11);
                            $pdf->Cell(111,6,utf8_decode('Arrétée la présente facture à la somme de :     '.str_replace(","," ", number_format($tab[$i]['montant'])).' F'),0,0,'L');

                            $pdf->SetFont('Helvetica','', 11);
                            $pdf->Cell(80,6,'',0,0,'L');



                            $pdf->Ln(6);
                        }elseif($tab[$i]['nature']=='TAL'){

                            $formatter = \NumberFormatter::create('fr_FR', \NumberFormatter::SPELLOUT);
                            $formatter->setAttribute(\NumberFormatter::FRACTION_DIGITS, 0);
                            $formatter->setAttribute(\NumberFormatter::ROUNDING_MODE, \NumberFormatter::ROUND_HALFUP);
                            $ttlettre= $formatter->format($tab[$i]['montant']);   // un million cinq cent vingt-deux mille cinq cent trente
                            $pdf->SetFont('Helvetica','B', 11);
                            $pdf->Cell(191,6,utf8_decode('     '.strtoupper($ttlettre).' FCFA'),0,0,'L');
                            $pdf->SetFont('Helvetica','', 11);
                            $pdf->Ln(6);
                        }elseif($tab[$i]['nature']=='CDT'){

                            $pdf->SetFont('Helvetica','U', 11);
                            $pdf->Cell(191,6,utf8_decode('CONDITIONS DE PAIEMENT :'),0,0,'L');
                            $pdf->SetFont('Helvetica','', 11);
                            $pdf->Ln(6);
                        }elseif($tab[$i]['nature']=='DLM'){


                            $pdf->SetFont('Helvetica','', 11);
                            $pdf->Cell(56,6,utf8_decode('DATE LIMITE DE PAIEMENT :  '),0,0,'L');

                            $pdf->SetFont('Helvetica','B', 9);
                            $pdf->Cell(135,6,utf8_decode('20 jours après réception de la facture'),0,0,'L');

                            $pdf->SetFont('Helvetica','', 11);

                            $pdf->Ln(6);
                        }elseif($tab[$i]['nature']=='VRM'){

                            $vrmfacture=$facture->getVirement();
                            $pdf->SetFont('Helvetica','', 11);
                            $pdf->Cell(24,6,utf8_decode('VIREMENT : '),0,0,'L');

                            $pdf->SetFont('Helvetica','B', 9);
                            $pdf->Cell(167,6,utf8_decode($vrmfacture),0,0,'L');

                            $pdf->SetFont('Helvetica','', 11);

                            $pdf->Ln(6);
                        }elseif($tab[$i]['nature']=='ACQ'){

                            $pdf->SetFont('Helvetica','', 9);
                            $pdf->Cell(191,6,utf8_decode('Chèque à libeller au nom de EMS SENEGAL'),0,0,'L');
                            $pdf->SetFont('Helvetica','', 11);
                            $pdf->Ln(6);
                        }elseif($tab[$i]['nature']=='LAU1'){


                            $pdf->SetFont('Helvetica','', 10);
                            $pdf->Cell(95,6,utf8_decode("LE DIRECTEUR DE L'ADMINISTRATION DES"),0,0,'L');

                            $pdf->SetFont('Helvetica','', 10);


                            if($autDG==null){
                                $pdf->Cell(96,6,'LE DIRECTEUR GENERAL',0,0,'L');
                            }else{

                                if($autDG->getTitre()=='DGTITULAIRE'){
                                    $pdf->Cell(96,6,'LE DIRECTEUR GENERAL',0,0,'L');
                                }else{
                                    $pdf->Cell(96,6,'LE DIRECTEUR GENERAL PAR INTERIM',0,0,'L');
                                }

                            }

                            $pdf->Ln(4);
                        }elseif($tab[$i]['nature']=='LAU2'){


                            $pdf->SetFont('Helvetica','', 10);
                            if($autDAFC==null){
                                $pdf->Cell(95,6,'LE DIRECTEUR GENERAL',0,0,'L');
                            }else{

                                if($autDAFC->getTitre()=='DAFCTITULAIRE'){
                                    $pdf->Cell(95,6,'FINANCES ET DE LA COMPTABILITE',0,0,'L');
                                }else{
                                    $pdf->Cell(95,6,'FINANCES ET DE LA COMPTABILITE PAR INTERIM',0,0,'L');
                                }

                            }

                            $pdf->SetFont('Helvetica','', 10);
                            $pdf->Cell(96,6,'',0,0,'L');

                            $pdf->Ln(6);
                        }elseif($tab[$i]['nature']=='LAU3'){


                            $pdf->SetFont('Helvetica','', 10);
                            if($autDAFC!=null){
                                $nomdaf=$autDAFC->getNom();
                                $pdf->Cell(95,6,utf8_decode(strtoupper($nomdaf)),0,0,'L');
                            }else{
                                $pdf->Cell(95,6,'',0,0,'L');
                            }

                            $pdf->SetFont('Helvetica','', 10);
                            if($autDG!=null){
                                $nomdg=$autDG->getNom();
                                $pdf->Cell(96,6,utf8_decode(strtoupper($nomdg)),0,0,'L');
                            }else{
                                $pdf->Cell(96,6,'',0,0,'L');
                            }

                            $pdf->Ln(6);
                        }



                    }




                    for($i=$reperes;$i<37;$i++)
                    {
                        $pdf->Cell(103,6,'',0,0,'C');

                        $pdf->SetFont('Helvetica','B', 11);
                        $pdf->Cell(60,6,'',0,0,'L');

                        $pdf->SetFont('Helvetica','', 11);
                        $pdf->Cell(28,6,'',0,0,'R');
                        $pdf->Ln(6);
                    }

                    // $nbrp=$nbrp+1;

                    //Footer
                    $PageNo= $PageNo+1;
                    $pdf->Ln(6);
                    $pdf->SetFont('Helvetica','I',9);
                    $infopieds1=utf8_decode('Domaine SODIDA, lot n° 49 Dakar Tél. (221) 869.01.01 / Fax : 869.01.02 BP: 15 305 Email : ems@ems.sn');
                    $infopieds2='RC SN DK 2008 M19490-NINEA 002591536 2G3';
                    //$infopieds=$infopieds1."\n".$infopieds2;
                    $pdf->Cell(163,6,$infopieds1,'TR',0,'C');
                    $pdf->SetFont('Helvetica','B',9);
                    $pdf->Cell(28,6,'Page '.  $PageNo." sur ".$nbpage,0,0,'C');
                    $pdf->Ln(4);
                    $pdf->SetFont('Helvetica','I',9);
                    $pdf->Cell(163,6,$infopieds2,'R',0,'C');
                    $pdf->Cell(28,6,'',0,0,'C');

                    //New page
                    $pdf->AddPage();
                    $pdf->SetFont('Helvetica','', 11);
                    //Header page
                    $pdf->Image("img/logo-ems.jpg",1,null,50,12);
                    $pdf->Ln(5);



//Ligne Total
                    $pdf->Cell(103,6,'',0,0,'C');

                    $pdf->SetFont('Helvetica','B', 11);
                    $pdf->Cell(60,6,'Total',1,0,'L');

                    $pdf->SetFont('Helvetica','', 11);
                    $pdf->Cell(28,6,utf8_decode(str_replace(","," ", number_format($tab[$reperes-4]['montant']))),1,0,'R');
                    $pdf->Ln(6);

                    //Ligne Montant HT
                    $pdf->Cell(103,6,'',0,0,'C');

                    $pdf->SetFont('Helvetica','B', 11);
                    $pdf->Cell(60,6,'Montant HT',1,0,'L');

                    $pdf->SetFont('Helvetica','', 11);
                    $pdf->Cell(28,6,utf8_decode(str_replace(","," ", number_format($tab[$reperes-3]['montant']))),1,0,'R');
                    $pdf->Ln(6);

                    //Ligne TVA
                    $pdf->Cell(103,6,'',0,0,'C');

                    $pdf->SetFont('Helvetica','B', 11);
                    $pdf->Cell(60,6,'TVA',1,0,'L');

                    $pdf->SetFont('Helvetica','', 11);
                    $pdf->Cell(28,6,utf8_decode(str_replace(","," ", number_format($tab[$reperes-2]['montant']))),1,0,'R');
                    $pdf->Ln(6);
                    //Ligne TTC
                    $pdf->Cell(103,6,'',0,0,'C');

                    $pdf->SetFont('Helvetica','B', 11);
                    $pdf->Cell(60,6,'Total TTC',1,0,'L');

                    $pdf->SetFont('Helvetica','', 11);
                    $pdf->Cell(28,6,utf8_decode(str_replace(","," ", number_format($tab[$reperes-1]['montant']))),1,0,'R');
                    $pdf->Ln(6);



                    for($i=$reperes; $i<count($tab); $i++){


                        if($tab[$i]['nature']=='LV'){


                            //$pdf->SetFont('Helvetica','', 11);
                            $pdf->Cell(191,6,'',0,0);
                            $pdf->Ln(6);
                        }elseif($tab[$i]['nature']=='TAC'){


                            $pdf->SetFont('Helvetica','B', 11);
                            $pdf->Cell(111,6,utf8_decode('Arrétée la présente facture à la somme de :     '.str_replace(","," ", number_format($tab[$i]['montant'])).' F'),0,0,'L');

                            $pdf->SetFont('Helvetica','', 11);
                            $pdf->Cell(80,6,'',0,0,'L');



                            $pdf->Ln(6);
                        }elseif($tab[$i]['nature']=='TAL'){

                            $formatter = \NumberFormatter::create('fr_FR', \NumberFormatter::SPELLOUT);
                            $formatter->setAttribute(\NumberFormatter::FRACTION_DIGITS, 0);
                            $formatter->setAttribute(\NumberFormatter::ROUNDING_MODE, \NumberFormatter::ROUND_HALFUP);
                            $ttlettre= $formatter->format($tab[$i]['montant']);   // un million cinq cent vingt-deux mille cinq cent trente
                            $pdf->SetFont('Helvetica','B', 11);
                            $pdf->Cell(191,6,utf8_decode('     '.strtoupper($ttlettre).' FCFA'),0,0,'L');
                            $pdf->SetFont('Helvetica','', 11);
                            $pdf->Ln(6);
                        }elseif($tab[$i]['nature']=='CDT'){

                            $pdf->SetFont('Helvetica','U', 11);
                            $pdf->Cell(191,6,utf8_decode('CONDITIONS DE PAIEMENT :'),0,0,'L');
                            $pdf->SetFont('Helvetica','', 11);
                            $pdf->Ln(6);
                        }elseif($tab[$i]['nature']=='DLM'){


                            $pdf->SetFont('Helvetica','', 11);
                            $pdf->Cell(56,6,utf8_decode('DATE LIMITE DE PAIEMENT :  '),0,0,'L');

                            $pdf->SetFont('Helvetica','B', 9);
                            $pdf->Cell(135,6,utf8_decode('20 jours après réception de la facture'),0,0,'L');

                            $pdf->SetFont('Helvetica','', 11);

                            $pdf->Ln(6);
                        }elseif($tab[$i]['nature']=='VRM'){

                            $vrmfacture=$facture->getVirement();
                            $pdf->SetFont('Helvetica','', 11);
                            $pdf->Cell(24,6,utf8_decode('VIREMENT : '),0,0,'L');

                            $pdf->SetFont('Helvetica','B', 9);
                            $pdf->Cell(167,6,utf8_decode($vrmfacture),0,0,'L');

                            $pdf->SetFont('Helvetica','', 11);

                            $pdf->Ln(6);
                        }elseif($tab[$i]['nature']=='ACQ'){

                            $pdf->SetFont('Helvetica','', 9);
                            $pdf->Cell(191,6,utf8_decode('Chèque à libeller au nom de EMS SENEGAL'),0,0,'L');
                            $pdf->SetFont('Helvetica','', 11);
                            $pdf->Ln(6);
                        }elseif($tab[$i]['nature']=='LAU1'){


                            $pdf->SetFont('Helvetica','', 10);
                            $pdf->Cell(95,6,utf8_decode("LE DIRECTEUR DE L'ADMINISTRATION DES"),0,0,'L');

                            $pdf->SetFont('Helvetica','', 10);


                            if($autDG==null){
                                $pdf->Cell(96,6,'LE DIRECTEUR GENERAL',0,0,'L');
                            }else{

                                if($autDG->getTitre()=='DGTITULAIRE'){
                                    $pdf->Cell(96,6,'LE DIRECTEUR GENERAL',0,0,'L');
                                }else{
                                    $pdf->Cell(96,6,'LE DIRECTEUR GENERAL PAR INTERIM',0,0,'L');
                                }

                            }

                            $pdf->Ln(4);
                        }elseif($tab[$i]['nature']=='LAU2'){


                            $pdf->SetFont('Helvetica','', 10);
                            if($autDAFC==null){
                                $pdf->Cell(95,6,'LE DIRECTEUR GENERAL',0,0,'L');
                            }else{

                                if($autDAFC->getTitre()=='DAFCTITULAIRE'){
                                    $pdf->Cell(95,6,'FINANCES ET DE LA COMPTABILITE',0,0,'L');
                                }else{
                                    $pdf->Cell(95,6,'FINANCES ET DE LA COMPTABILITE PAR INTERIM',0,0,'L');
                                }

                            }

                            $pdf->SetFont('Helvetica','', 10);
                            $pdf->Cell(96,6,'',0,0,'L');

                            $pdf->Ln(6);
                        }elseif($tab[$i]['nature']=='LAU3'){


                            $pdf->SetFont('Helvetica','', 10);
                            if($autDAFC!=null){
                                $nomdaf=$autDAFC->getNom();
                                $pdf->Cell(95,6,utf8_decode(strtoupper($nomdaf)),0,0,'L');
                            }else{
                                $pdf->Cell(95,6,'',0,0,'L');
                            }

                            $pdf->SetFont('Helvetica','', 10);
                            if($autDG!=null){
                                $nomdg=$autDG->getNom();
                                $pdf->Cell(96,6,utf8_decode(strtoupper($nomdg)),0,0,'L');
                            }else{
                                $pdf->Cell(96,6,'',0,0,'L');
                            }

                            $pdf->Ln(6);
                        }
                    }




                    for($i=1; $i<21; $i++){



                        //$pdf->SetFont('Helvetica','', 11);
                        $pdf->Cell(191,6,'',0,0);
                        $pdf->Ln(6);

                    }







                }else{

                    // var_dump('En construction');die();
                    // $tabrestEnr    $tabrestPpg
                    $tabrestEnr=array();
                    $tabrestPpg=array();
                    for($i=33; $i<count($tab)-15; $i++){
                        //count($tab);
                        $tabrestEnr[$i-33]=$tab[$i];
                    }
                    //var_dump($tabrestEnr);die();

                    for($i=count($tab)-15; $i<count($tab); $i++){
                        //count($tab);
                        $tabrestPpg[$i-count($tab)+15]=$tab[$i];
                    }

                    $modtabEnr=count($tabrestEnr)%34;


                    $nbpage=(count($tabrestEnr)-count($tabrestEnr)%34)/34+2;

                    if($modtabEnr>24){
                        $nbpage=$nbpage+1;
                    }


                    for($i=0; $i<33; $i++){   //count($tab);

                        if($i!=0 and $i%34==0 and $nbpage>19){
                            // $nbrp=$nbrp+1;

                            //Footer page
                            $PageNo= $PageNo+1;
                            $pdf->Ln(6);
                            $pdf->SetFont('Helvetica','I',9);
                            $infopieds1=utf8_decode('Domaine SODIDA, lot n° 49 Dakar Tél. (221) 869.01.01 / Fax : 869.01.02 BP: 15 305 Email : ems@ems.sn');
                            $infopieds2='RC SN DK 2008 M19490-NINEA 002591536 2G3';
                            //$infopieds=$infopieds1."\n".$infopieds2;
                            $pdf->Cell(163,6,$infopieds1,'TR',0,'C');
                            $pdf->SetFont('Helvetica','B',9);
                            $pdf->Cell(28,6,'Page '.  $PageNo." sur ".$nbpage,0,0,'C');
                            $pdf->Ln(4);
                            $pdf->SetFont('Helvetica','I',9);
                            $pdf->Cell(163,6,$infopieds2,'R',0,'C');
                            $pdf->Cell(28,6,'',0,0,'C');

                            //New page
                            $pdf->AddPage();
                            $pdf->SetFont('Helvetica','', 11);
                            //Header page
                            $pdf->Image("img/logo-ems.jpg",1,null,50,12);
                            $pdf->Ln(5);
                        }



                        if($tab[$i]['nature']=='PR'){
                            $pdf->Cell(103,6,'',0,0,'C');
                            $tetx='Période facturée :';
                            $pdf->SetFont('Helvetica','B', 11);
                            $pdf->Cell(58,6,utf8_decode($tetx),0,0,'R');

                            $pdf->SetFont('Helvetica','', 11);
                            $pdf->Cell(30,6,$tab[$i]['code'],0,0,'L');
                            $pdf->Ln(6);
                        }elseif($tab[$i]['nature']=='NAB'){

                            $pdf->SetFont('Helvetica','B', 11);
                            $str= utf8_encode($this->removeAccents($tab[$i]['code']));
                            $pdf->MultiCell(103,6,strtoupper($str),0,'L');

                            $pdf->SetFont('Helvetica','', 11);
                            $pdf->MultiCell(88,6,'',0);
                        }elseif($tab[$i]['nature']=='AAB'){

                            $pdf->SetFont('Helvetica','B', 11);
                            //$pdf->Cell(103,6,utf8_decode($tab[$i]['code']),1,0,'L');

                            $str= utf8_encode($this->removeAccents($tab[$i]['code']));
                            $pdf->MultiCell(103,6,strtoupper($str),0,'L');

                            $pdf->SetFont('Helvetica','', 11);
                            //$pdf->Cell(88,6,'',0,0);
                            $pdf->MultiCell(88,6,'',0);
                        }elseif($tab[$i]['nature']=='LV'){


                            //$pdf->SetFont('Helvetica','', 11);
                            $pdf->Cell(191,6,'',0,0);
                            $pdf->Ln(6);
                        }elseif($tab[$i]['nature']=='NFA'){

                            $pdf->SetFont('Helvetica','B', 11);
                            $pdf->Cell(18,6,'Facture :',0,0,'L');

                            $pdf->SetFont('Helvetica','', 11);
                            $numfacture=$facture->getNumfacture();
                            $pdf->Cell(108,6,$numfacture.'/'.$mois,0,0,'L');

                            $pdf->SetFont('Helvetica','B', 11);
                            $pdf->Cell(30,6,utf8_decode("Date d'édition :"),0,0,'L');

                            $pdf->SetFont('Helvetica','', 11);
                            //$dateeditionfacture=$facture->getDateedition();
                            $pdf->Cell(30,6,$djomois,0,0);
                            //$pdf->MultiCell(88,4,'',0);
                            $pdf->Ln(6);
                        }elseif($tab[$i]['nature']=='IN'){
                            $pdf->Ln(6);
                            $pdf->SetFont('Helvetica','B', 11);
                            $pdf->Cell(103,6,'INTERNATIONAL',0,0,'L');

                            $pdf->SetFont('Helvetica','', 11);
                            $pdf->Cell(88,6,'',0,0);
                            $pdf->Ln(6);
                        }elseif($tab[$i]['nature']=='INE'){
                            $pdf->SetFont('Helvetica','B', 11);
                            $pdf->Cell(23,6,utf8_decode('Date'),1,0,'C');
                            $pdf->Cell(40,6,utf8_decode('Lieu de dépôt'),1,0,'C');
                            $pdf->Cell(40,6,utf8_decode('N° dépot'),1,0,'C');
                            $pdf->Cell(40,6,utf8_decode('Destination'),1,0,'C');
                            $pdf->Cell(20,6,utf8_decode('Poids/Kg'),1,0,'C');
                            $pdf->Cell(28,6,utf8_decode('Montant'),1,0,'C');
                            $pdf->SetFont('Helvetica','', 11);
                            $pdf->Ln(6);
                        }elseif($tab[$i]['nature']=='EN'){
                            $pdf->Cell(23,6,utf8_decode($tab[$i]['date']),1,0,'C');
                            $pdf->Cell(40,6,strtoupper(utf8_decode($tab[$i]['agence'])),1,0,'L');

                            $pdf->Cell(40,6,strtoupper(utf8_decode($tab[$i]['code'])),1,0,'C');

                            if($tab[$i]['destfacture']!='etr'){
                                $pdf->Cell(40,6,strtoupper(utf8_decode($tab[$i]['destfacture'])),1,0,'L');
                            }else{
                                $pdf->Cell(40,6,strtoupper(utf8_decode($tab[$i]['destination'])),1,0,'L');
                            }


                            $pdf->Cell(20,6,utf8_decode($tab[$i]['poids']),1,0,'C');

                            $pdf->Cell(28,6,utf8_decode(str_replace(","," ", number_format($tab[$i]['montant']))),1,0,'R');
                            $pdf->Ln(6);
                        }elseif($tab[$i]['nature']=='NA'){
                            $pdf->Ln(6);
                            $pdf->SetFont('Helvetica','B', 11);
                            $pdf->Cell(103,6,'NATIONAL',0,0,'L');

                            $pdf->SetFont('Helvetica','', 11);
                            $pdf->Cell(88,6,'',0,0);
                            $pdf->Ln(6);
                        }elseif($tab[$i]['nature']=='NAE'){
                            $pdf->SetFont('Helvetica','B', 11);
                            $pdf->Cell(23,6,utf8_decode('Date'),1,0,'C');
                            $pdf->Cell(40,6,utf8_decode('Lieu de dépôt'),1,0,'C');
                            $pdf->Cell(40,6,utf8_decode('N° dépot'),1,0,'C');
                            $pdf->Cell(40,6,utf8_decode('Destination'),1,0,'C');
                            $pdf->Cell(20,6,utf8_decode('Poids/Kg'),1,0,'C');
                            $pdf->Cell(28,6,utf8_decode('Montant'),1,0,'C');
                            $pdf->SetFont('Helvetica','', 11);
                            $pdf->Ln(6);
                        }elseif($tab[$i]['nature']=='TNI'){
                            $pdf->Cell(103,6,'',0,0,'C');

                            $pdf->SetFont('Helvetica','B', 11);
                            $pdf->Cell(60,6,'Total',1,0,'L');

                            $pdf->SetFont('Helvetica','', 11);
                            $pdf->Cell(28,6,utf8_decode(str_replace(","," ", number_format($tab[$i]['montant']))),1,0,'R');
                            $pdf->Ln(6);
                        }elseif($tab[$i]['nature']=='TNA'){
                            $pdf->Cell(103,6,'',0,0,'C');

                            $pdf->SetFont('Helvetica','B', 11);
                            $pdf->Cell(60,6,'Total',1,0,'L');

                            $pdf->SetFont('Helvetica','', 11);
                            $pdf->Cell(28,6,utf8_decode(str_replace(","," ", number_format($tab[$i]['montant']))),1,0,'R');
                            $pdf->Ln(6);
                        }elseif($tab[$i]['nature']=='TT'){
                            $pdf->Cell(103,6,'',0,0,'C');

                            $pdf->SetFont('Helvetica','B', 11);
                            $pdf->Cell(60,6,'Montant HT',1,0,'L');

                            $pdf->SetFont('Helvetica','', 11);
                            $pdf->Cell(28,6,utf8_decode(str_replace(","," ", number_format($tab[$i]['montant']))),1,0,'R');
                            $pdf->Ln(6);
                        }elseif($tab[$i]['nature']=='TVA'){
                            $pdf->Cell(103,6,'',0,0,'C');

                            $pdf->SetFont('Helvetica','B', 11);
                            $pdf->Cell(60,6,'TVA',1,0,'L');

                            $pdf->SetFont('Helvetica','', 11);
                            $pdf->Cell(28,6,utf8_decode(str_replace(","," ", number_format($tab[$i]['montant']))),1,0,'R');
                            $pdf->Ln(6);
                        }elseif($tab[$i]['nature']=='TTC'){
                            $pdf->Cell(103,6,'',0,0,'C');

                            $pdf->SetFont('Helvetica','B', 11);
                            $pdf->Cell(60,6,'Total TTC',1,0,'L');

                            $pdf->SetFont('Helvetica','', 11);
                            $pdf->Cell(28,6,utf8_decode(str_replace(","," ", number_format($tab[$i]['montant']))),1,0,'R');
                            $pdf->Ln(6);
                        }elseif($tab[$i]['nature']=='TAC'){


                            $pdf->SetFont('Helvetica','B', 11);
                            $pdf->Cell(111,6,utf8_decode('Arrétée la présente facture à la somme de :     '.str_replace(","," ", number_format($tab[$i]['montant'])).' F'),0,0,'L');

                            $pdf->SetFont('Helvetica','', 11);
                            $pdf->Cell(80,6,'',0,0,'L');



                            $pdf->Ln(6);
                        }elseif($tab[$i]['nature']=='TAL'){

                            $formatter = \NumberFormatter::create('fr_FR', \NumberFormatter::SPELLOUT);
                            $formatter->setAttribute(\NumberFormatter::FRACTION_DIGITS, 0);
                            $formatter->setAttribute(\NumberFormatter::ROUNDING_MODE, \NumberFormatter::ROUND_HALFUP);
                            $ttlettre= $formatter->format($tab[$i]['montant']);   // un million cinq cent vingt-deux mille cinq cent trente
                            $pdf->SetFont('Helvetica','B', 11);
                            $pdf->Cell(191,6,utf8_decode('     '.strtoupper($ttlettre).' FCFA'),0,0,'L');
                            $pdf->SetFont('Helvetica','', 11);
                            $pdf->Ln(6);
                        }elseif($tab[$i]['nature']=='CDT'){

                            $pdf->SetFont('Helvetica','U', 11);
                            $pdf->Cell(191,6,utf8_decode('CONDITIONS DE PAIEMENT :'),0,0,'L');
                            $pdf->SetFont('Helvetica','', 11);
                            $pdf->Ln(6);
                        }elseif($tab[$i]['nature']=='DLM'){


                            $pdf->SetFont('Helvetica','', 11);
                            $pdf->Cell(56,6,utf8_decode('DATE LIMITE DE PAIEMENT :  '),0,0,'L');

                            $pdf->SetFont('Helvetica','B', 9);
                            $pdf->Cell(135,6,utf8_decode('20 jours après réception de la facture'),0,0,'L');

                            $pdf->SetFont('Helvetica','', 11);

                            $pdf->Ln(6);
                        }elseif($tab[$i]['nature']=='VRM'){

                            $vrmfacture=$facture->getVirement();
                            $pdf->SetFont('Helvetica','', 11);
                            $pdf->Cell(24,6,utf8_decode('VIREMENT : '),0,0,'L');

                            $pdf->SetFont('Helvetica','B', 9);
                            $pdf->Cell(167,6,utf8_decode($vrmfacture),0,0,'L');

                            $pdf->SetFont('Helvetica','', 11);

                            $pdf->Ln(6);
                        }elseif($tab[$i]['nature']=='ACQ'){

                            $pdf->SetFont('Helvetica','', 9);
                            $pdf->Cell(191,6,utf8_decode('Chèque à libeller au nom de EMS SENEGAL'),0,0,'L');
                            $pdf->SetFont('Helvetica','', 11);
                            $pdf->Ln(6);
                        }elseif($tab[$i]['nature']=='LAU1'){


                            $pdf->SetFont('Helvetica','', 10);
                            $pdf->Cell(95,6,utf8_decode("LE DIRECTEUR DE L'ADMINISTRATION DES"),0,0,'L');

                            $pdf->SetFont('Helvetica','', 10);


                            if($autDG==null){
                                $pdf->Cell(96,6,'LE DIRECTEUR GENERAL',0,0,'L');
                            }else{

                                if($autDG->getTitre()=='DGTITULAIRE'){
                                    $pdf->Cell(96,6,'LE DIRECTEUR GENERAL',0,0,'L');
                                }else{
                                    $pdf->Cell(96,6,'LE DIRECTEUR GENERAL PAR INTERIM',0,0,'L');
                                }

                            }

                            $pdf->Ln(4);
                        }elseif($tab[$i]['nature']=='LAU2'){


                            $pdf->SetFont('Helvetica','', 10);
                            if($autDAFC==null){
                                $pdf->Cell(95,6,'LE DIRECTEUR GENERAL',0,0,'L');
                            }else{

                                if($autDAFC->getTitre()=='DAFCTITULAIRE'){
                                    $pdf->Cell(95,6,'FINANCES ET DE LA COMPTABILITE',0,0,'L');
                                }else{
                                    $pdf->Cell(95,6,'FINANCES ET DE LA COMPTABILITE PAR INTERIM',0,0,'L');
                                }

                            }

                            $pdf->SetFont('Helvetica','', 10);
                            $pdf->Cell(96,6,'',0,0,'L');

                            $pdf->Ln(6);
                        }elseif($tab[$i]['nature']=='LAU3'){


                            $pdf->SetFont('Helvetica','', 10);
                            if($autDAFC!=null){
                                $nomdaf=$autDAFC->getNom();
                                $pdf->Cell(95,6,utf8_decode(strtoupper($nomdaf)),0,0,'L');
                            }else{
                                $pdf->Cell(95,6,'',0,0,'L');
                            }

                            $pdf->SetFont('Helvetica','', 10);
                            if($autDG!=null){
                                $nomdg=$autDG->getNom();
                                $pdf->Cell(96,6,utf8_decode(strtoupper($nomdg)),0,0,'L');
                            }else{
                                $pdf->Cell(96,6,'',0,0,'L');
                            }

                            $pdf->Ln(6);
                        }



                    }



                    //Footer page
                    $PageNo= $PageNo+1;
                    $pdf->Ln(6);
                    $pdf->SetFont('Helvetica','I',9);
                    $infopieds1=utf8_decode('Domaine SODIDA, lot n° 49 Dakar Tél. (221) 869.01.01 / Fax : 869.01.02 BP: 15 305 Email : ems@ems.sn');
                    $infopieds2='RC SN DK 2008 M19490-NINEA 002591536 2G3';
                    //$infopieds=$infopieds1."\n".$infopieds2;
                    $pdf->Cell(163,6,$infopieds1,'TR',0,'C');
                    $pdf->SetFont('Helvetica','B',9);
                    $pdf->Cell(28,6,'Page '.  $PageNo." sur ".$nbpage,0,0,'C');
                    $pdf->Ln(4);
                    $pdf->SetFont('Helvetica','I',9);
                    $pdf->Cell(163,6,$infopieds2,'R',0,'C');
                    $pdf->Cell(28,6,'',0,0,'C');

                    //New page
                    $pdf->AddPage();
                    $pdf->SetFont('Helvetica','', 11);
                    //Header page
                    $pdf->Image("img/logo-ems.jpg",1,null,50,12);
                    $pdf->Ln(5);





                    for($i=0; $i<count($tabrestEnr)-$modtabEnr; $i++){   //count($tabrestEnr);

                        if($i!=0 and $i%34==0){




                            // $nbrp=$nbrp+1;

                            $pdf->Cell(191,6,'',0,0);
                            $pdf->Ln(6);
                            $pdf->Cell(191,6,'',0,0);
                            $pdf->Ln(6);
                            $pdf->Cell(191,6,'',0,0);
                            $pdf->Ln(6);
                            $pdf->Cell(191,6,'',0,0);
                            $pdf->Ln(6);




                            //Footer page
                            $PageNo= $PageNo+1;
                            $pdf->Ln(6);
                            $pdf->SetFont('Helvetica','I',9);
                            $infopieds1=utf8_decode('Domaine SODIDA, lot n° 49 Dakar Tél. (221) 869.01.01 / Fax : 869.01.02 BP: 15 305 Email : ems@ems.sn');
                            $infopieds2='RC SN DK 2008 M19490-NINEA 002591536 2G3';
                            //$infopieds=$infopieds1."\n".$infopieds2;
                            $pdf->Cell(163,6,$infopieds1,'TR',0,'C');
                            $pdf->SetFont('Helvetica','B',9);
                            $pdf->Cell(28,6,'Page '.  $PageNo." sur ".$nbpage,0,0,'C');
                            $pdf->Ln(4);
                            $pdf->SetFont('Helvetica','I',9);
                            $pdf->Cell(163,6,$infopieds2,'R',0,'C');
                            $pdf->Cell(28,6,'',0,0,'C');

                            //New page
                            $pdf->AddPage();
                            $pdf->SetFont('Helvetica','', 11);
                            //Header page
                            $pdf->Image("img/logo-ems.jpg",1,null,50,12);
                            $pdf->Ln(5);
                        }



                        if($tabrestEnr[$i]['nature']=='LV'){


                            //$pdf->SetFont('Helvetica','', 11);
                            $pdf->Cell(191,6,'',0,0);
                            $pdf->Ln(6);
                        }elseif($tabrestEnr[$i]['nature']=='IN'){
                            $pdf->Ln(6);
                            $pdf->SetFont('Helvetica','B', 11);
                            $pdf->Cell(103,6,'INTERNATIONAL',0,0,'L');

                            $pdf->SetFont('Helvetica','', 11);
                            $pdf->Cell(88,6,'',0,0);
                            $pdf->Ln(6);
                        }elseif($tabrestEnr[$i]['nature']=='INE'){
                            $pdf->SetFont('Helvetica','B', 11);
                            $pdf->Cell(23,6,utf8_decode('Date'),1,0,'C');
                            $pdf->Cell(40,6,utf8_decode('Lieu de dépôt'),1,0,'C');
                            $pdf->Cell(40,6,utf8_decode('N° dépot'),1,0,'C');
                            $pdf->Cell(40,6,utf8_decode('Destination'),1,0,'C');
                            $pdf->Cell(20,6,utf8_decode('Poids/Kg'),1,0,'C');
                            $pdf->Cell(28,6,utf8_decode('Montant'),1,0,'C');
                            $pdf->SetFont('Helvetica','', 11);
                            $pdf->Ln(6);
                        }elseif($tabrestEnr[$i]['nature']=='EN'){
                            $pdf->Cell(23,6,utf8_decode($tabrestEnr[$i]['date']),1,0,'C');
                            $pdf->Cell(40,6,strtoupper(utf8_decode($tabrestEnr[$i]['agence'])),1,0,'L');

                            $pdf->Cell(40,6,strtoupper(utf8_decode($tabrestEnr[$i]['code'])),1,0,'C');

                            if($tabrestEnr[$i]['destfacture']!='etr'){
                                $pdf->Cell(40,6,strtoupper(utf8_decode($tabrestEnr[$i]['destfacture'])),1,0,'L');
                            }else{
                                $pdf->Cell(40,6,strtoupper(utf8_decode($tabrestEnr[$i]['destination'])),1,0,'L');
                            }


                            $pdf->Cell(20,6,utf8_decode($tabrestEnr[$i]['poids']),1,0,'C');

                            $pdf->Cell(28,6,utf8_decode(str_replace(","," ", number_format($tabrestEnr[$i]['montant']))),1,0,'R');
                            $pdf->Ln(6);
                        }elseif($tabrestEnr[$i]['nature']=='NA'){
                            $pdf->Ln(6);
                            $pdf->SetFont('Helvetica','B', 11);
                            $pdf->Cell(103,6,'NATIONAL',0,0,'L');

                            $pdf->SetFont('Helvetica','', 11);
                            $pdf->Cell(88,6,'',0,0);
                            $pdf->Ln(6);
                        }elseif($tabrestEnr[$i]['nature']=='NAE'){
                            $pdf->SetFont('Helvetica','B', 11);
                            $pdf->Cell(23,6,utf8_decode('Date'),1,0,'C');
                            $pdf->Cell(40,6,utf8_decode('Lieu de dépôt'),1,0,'C');
                            $pdf->Cell(40,6,utf8_decode('N° dépot'),1,0,'C');
                            $pdf->Cell(40,6,utf8_decode('Destination'),1,0,'C');
                            $pdf->Cell(20,6,utf8_decode('Poids/Kg'),1,0,'C');
                            $pdf->Cell(28,6,utf8_decode('Montant'),1,0,'C');
                            $pdf->SetFont('Helvetica','', 11);
                            $pdf->Ln(6);
                        }elseif($tabrestEnr[$i]['nature']=='TNI'){
                            $pdf->Cell(103,6,'',0,0,'C');

                            $pdf->SetFont('Helvetica','B', 11);
                            $pdf->Cell(60,6,'Total',1,0,'L');

                            $pdf->SetFont('Helvetica','', 11);
                            $pdf->Cell(28,6,utf8_decode(str_replace(","," ", number_format($tabrestEnr[$i]['montant']))),1,0,'R');
                            $pdf->Ln(6);
                        }elseif($tabrestEnr[$i]['nature']=='TNA'){
                            $pdf->Cell(103,6,'',0,0,'C');

                            $pdf->SetFont('Helvetica','B', 11);
                            $pdf->Cell(60,6,'Total',1,0,'L');

                            $pdf->SetFont('Helvetica','', 11);
                            $pdf->Cell(28,6,utf8_decode(str_replace(","," ", number_format($tabrestEnr[$i]['montant']))),1,0,'R');
                            $pdf->Ln(6);
                        }elseif($tabrestEnr[$i]['nature']=='TT'){
                            $pdf->Cell(103,6,'',0,0,'C');

                            $pdf->SetFont('Helvetica','B', 11);
                            $pdf->Cell(60,6,'Montant HT',1,0,'L');

                            $pdf->SetFont('Helvetica','', 11);
                            $pdf->Cell(28,6,utf8_decode(str_replace(","," ", number_format($tabrestEnr[$i]['montant']))),1,0,'R');
                            $pdf->Ln(6);
                        }elseif($tabrestEnr[$i]['nature']=='TVA'){
                            $pdf->Cell(103,6,'',0,0,'C');

                            $pdf->SetFont('Helvetica','B', 11);
                            $pdf->Cell(60,6,'TVA',1,0,'L');

                            $pdf->SetFont('Helvetica','', 11);
                            $pdf->Cell(28,6,utf8_decode(str_replace(","," ", number_format($tabrestEnr[$i]['montant']))),1,0,'R');
                            $pdf->Ln(6);
                        }elseif($tabrestEnr[$i]['nature']=='TTC'){
                            $pdf->Cell(103,6,'',0,0,'C');

                            $pdf->SetFont('Helvetica','B', 11);
                            $pdf->Cell(60,6,'Total TTC',1,0,'L');

                            $pdf->SetFont('Helvetica','', 11);
                            $pdf->Cell(28,6,utf8_decode(str_replace(","," ", number_format($tabrestEnr[$i]['montant']))),1,0,'R');
                            $pdf->Ln(6);
                        }



                    }








                    if($modtabEnr<=24){
                        //var_dump($modtabEnr);die();



                        for($i=count($tabrestEnr)-$modtabEnr; $i<count($tabrestEnr); $i++){   //count($tabrestEnr);

                            if($i!=0 and $i%34==0){




                                // $nbrp=$nbrp+1;

                                //Footer page
                                $PageNo= $PageNo+1;
                                $pdf->Ln(6);
                                $pdf->SetFont('Helvetica','I',9);
                                $infopieds1=utf8_decode('Domaine SODIDA, lot n° 49 Dakar Tél. (221) 869.01.01 / Fax : 869.01.02 BP: 15 305 Email : ems@ems.sn');
                                $infopieds2='RC SN DK 2008 M19490-NINEA 002591536 2G3';
                                //$infopieds=$infopieds1."\n".$infopieds2;
                                $pdf->Cell(163,6,$infopieds1,'TR',0,'C');
                                $pdf->SetFont('Helvetica','B',9);
                                $pdf->Cell(28,6,'Page '.  $PageNo." sur ".$nbpage,0,0,'C');
                                $pdf->Ln(4);
                                $pdf->SetFont('Helvetica','I',9);
                                $pdf->Cell(163,6,$infopieds2,'R',0,'C');
                                $pdf->Cell(28,6,'',0,0,'C');

                                //New page
                                $pdf->AddPage();
                                $pdf->SetFont('Helvetica','', 11);
                                //Header page
                                $pdf->Image("img/logo-ems.jpg",1,null,50,12);
                                $pdf->Ln(5);
                            }



                            if($tabrestEnr[$i]['nature']=='LV'){


                                //$pdf->SetFont('Helvetica','', 11);
                                $pdf->Cell(191,6,'',0,0);
                                $pdf->Ln(6);
                            }elseif($tabrestEnr[$i]['nature']=='IN'){
                                $pdf->Ln(6);
                                $pdf->SetFont('Helvetica','B', 11);
                                $pdf->Cell(103,6,'INTERNATIONAL',0,0,'L');

                                $pdf->SetFont('Helvetica','', 11);
                                $pdf->Cell(88,6,'',0,0);
                                $pdf->Ln(6);
                            }elseif($tabrestEnr[$i]['nature']=='INE'){
                                $pdf->SetFont('Helvetica','B', 11);
                                $pdf->Cell(23,6,utf8_decode('Date'),1,0,'C');
                                $pdf->Cell(40,6,utf8_decode('Lieu de dépôt'),1,0,'C');
                                $pdf->Cell(40,6,utf8_decode('N° dépot'),1,0,'C');
                                $pdf->Cell(40,6,utf8_decode('Destination'),1,0,'C');
                                $pdf->Cell(20,6,utf8_decode('Poids/Kg'),1,0,'C');
                                $pdf->Cell(28,6,utf8_decode('Montant'),1,0,'C');
                                $pdf->SetFont('Helvetica','', 11);
                                $pdf->Ln(6);
                            }elseif($tabrestEnr[$i]['nature']=='EN'){
                                $pdf->Cell(23,6,utf8_decode($tabrestEnr[$i]['date']),1,0,'C');
                                $pdf->Cell(40,6,strtoupper(utf8_decode($tabrestEnr[$i]['agence'])),1,0,'L');

                                $pdf->Cell(40,6,strtoupper(utf8_decode($tabrestEnr[$i]['code'])),1,0,'C');

                                if($tabrestEnr[$i]['destfacture']!='etr'){
                                    $pdf->Cell(40,6,strtoupper(utf8_decode($tabrestEnr[$i]['destfacture'])),1,0,'L');
                                }else{
                                    $pdf->Cell(40,6,strtoupper(utf8_decode($tabrestEnr[$i]['destination'])),1,0,'L');
                                }


                                $pdf->Cell(20,6,utf8_decode($tabrestEnr[$i]['poids']),1,0,'C');

                                $pdf->Cell(28,6,utf8_decode(str_replace(","," ", number_format($tabrestEnr[$i]['montant']))),1,0,'R');
                                $pdf->Ln(6);
                            }elseif($tabrestEnr[$i]['nature']=='NA'){
                                $pdf->Ln(6);
                                $pdf->SetFont('Helvetica','B', 11);
                                $pdf->Cell(103,6,'NATIONAL',0,0,'L');

                                $pdf->SetFont('Helvetica','', 11);
                                $pdf->Cell(88,6,'',0,0);
                                $pdf->Ln(6);
                            }elseif($tabrestEnr[$i]['nature']=='NAE'){
                                $pdf->SetFont('Helvetica','B', 11);
                                $pdf->Cell(23,6,utf8_decode('Date'),1,0,'C');
                                $pdf->Cell(40,6,utf8_decode('Lieu de dépôt'),1,0,'C');
                                $pdf->Cell(40,6,utf8_decode('N° dépot'),1,0,'C');
                                $pdf->Cell(40,6,utf8_decode('Destination'),1,0,'C');
                                $pdf->Cell(20,6,utf8_decode('Poids/Kg'),1,0,'C');
                                $pdf->Cell(28,6,utf8_decode('Montant'),1,0,'C');
                                $pdf->SetFont('Helvetica','', 11);
                                $pdf->Ln(6);
                            }elseif($tabrestEnr[$i]['nature']=='TNI'){
                                $pdf->Cell(103,6,'',0,0,'C');

                                $pdf->SetFont('Helvetica','B', 11);
                                $pdf->Cell(60,6,'Total',1,0,'L');

                                $pdf->SetFont('Helvetica','', 11);
                                $pdf->Cell(28,6,utf8_decode(str_replace(","," ", number_format($tabrestEnr[$i]['montant']))),1,0,'R');
                                $pdf->Ln(6);
                            }elseif($tabrestEnr[$i]['nature']=='TNA'){
                                $pdf->Cell(103,6,'',0,0,'C');

                                $pdf->SetFont('Helvetica','B', 11);
                                $pdf->Cell(60,6,'Total',1,0,'L');

                                $pdf->SetFont('Helvetica','', 11);
                                $pdf->Cell(28,6,utf8_decode(str_replace(","," ", number_format($tabrestEnr[$i]['montant']))),1,0,'R');
                                $pdf->Ln(6);
                            }elseif($tabrestEnr[$i]['nature']=='TT'){
                                $pdf->Cell(103,6,'',0,0,'C');

                                $pdf->SetFont('Helvetica','B', 11);
                                $pdf->Cell(60,6,'Montant HT',1,0,'L');

                                $pdf->SetFont('Helvetica','', 11);
                                $pdf->Cell(28,6,utf8_decode(str_replace(","," ", number_format($tabrestEnr[$i]['montant']))),1,0,'R');
                                $pdf->Ln(6);
                            }elseif($tabrestEnr[$i]['nature']=='TVA'){
                                $pdf->Cell(103,6,'',0,0,'C');

                                $pdf->SetFont('Helvetica','B', 11);
                                $pdf->Cell(60,6,'TVA',1,0,'L');

                                $pdf->SetFont('Helvetica','', 11);
                                $pdf->Cell(28,6,utf8_decode(str_replace(","," ", number_format($tabrestEnr[$i]['montant']))),1,0,'R');
                                $pdf->Ln(6);
                            }elseif($tabrestEnr[$i]['nature']=='TTC'){
                                $pdf->Cell(103,6,'',0,0,'C');

                                $pdf->SetFont('Helvetica','B', 11);
                                $pdf->Cell(60,6,'Total TTC',1,0,'L');

                                $pdf->SetFont('Helvetica','', 11);
                                $pdf->Cell(28,6,utf8_decode(str_replace(","," ", number_format($tabrestEnr[$i]['montant']))),1,0,'R');
                                $pdf->Ln(6);
                            }



                        }





                        //arret mod
                        for($i=0; $i<count($tabrestPpg); $i++){

                            if($i!=0 and $i%34==0){
                                // $nbrp=$nbrp+1;

                                //Footer page
                                $PageNo= $PageNo+1;
                                $pdf->Ln(6);
                                $pdf->SetFont('Helvetica','I',9);
                                $infopieds1=utf8_decode('Domaine SODIDA, lot n° 49 Dakar Tél. (221) 869.01.01 / Fax : 869.01.02 BP: 15 305 Email : ems@ems.sn');
                                $infopieds2='RC SN DK 2008 M19490-NINEA 002591536 2G3';
                                //$infopieds=$infopieds1."\n".$infopieds2;
                                $pdf->Cell(163,6,$infopieds1,'TR',0,'C');
                                $pdf->SetFont('Helvetica','B',9);
                                $pdf->Cell(28,6,'Page '. $PageNo." sur ".$nbpage,0,0,'C');
                                $pdf->Ln(4);
                                $pdf->SetFont('Helvetica','I',9);
                                $pdf->Cell(163,6,$infopieds2,'R',0,'C');
                                $pdf->Cell(28,6,'',0,0,'C');

                                //New page
                                $pdf->AddPage();
                                $pdf->SetFont('Helvetica','', 11);
                                //Header page
                                $pdf->Image("img/logo-ems.jpg",1,null,50,12);
                                $pdf->Ln(5);
                            }



                            if($tabrestPpg[$i]['nature']=='PR'){
                                $pdf->Cell(103,6,'',0,0,'C');
                                $tetx='Période facturée :';
                                $pdf->SetFont('Helvetica','B', 11);
                                $pdf->Cell(58,6,utf8_decode($tetx),0,0,'R');

                                $pdf->SetFont('Helvetica','', 11);
                                $pdf->Cell(30,6,$tabrestPpg[$i]['code'],0,0,'L');
                                $pdf->Ln(6);
                            }elseif($tabrestPpg[$i]['nature']=='NAB'){

                                $pdf->SetFont('Helvetica','B', 11);
                                $str= utf8_encode($this->removeAccents($tabrestPpg[$i]['code']));
                                $pdf->MultiCell(103,6,strtoupper($str),0,'L');

                                $pdf->SetFont('Helvetica','', 11);
                                $pdf->MultiCell(88,6,'',0);
                            }elseif($tabrestPpg[$i]['nature']=='AAB'){

                                $pdf->SetFont('Helvetica','B', 11);
                                //$pdf->Cell(103,6,utf8_decode($tabrestPpg[$i]['code']),1,0,'L');

                                $str= utf8_encode($this->removeAccents($tabrestPpg[$i]['code']));
                                $pdf->MultiCell(103,6,strtoupper($str),0,'L');

                                $pdf->SetFont('Helvetica','', 11);
                                //$pdf->Cell(88,6,'',0,0);
                                $pdf->MultiCell(88,6,'',0);
                            }elseif($tabrestPpg[$i]['nature']=='LV'){
                                if($modtabEnr>19 and $i!=count($tabrestPpg)-2 and $i!=count($tabrestPpg)-3) {
                                    //var_dump("ss");die();
                                }else{
                                    //$modtabEnr
                                    //$pdf->SetFont('Helvetica','', 11);
                                    $pdf->Cell(191,6,'',0,0);
                                    $pdf->Ln(6);
                                }

                            }elseif($tabrestPpg[$i]['nature']=='NFA'){

                                $pdf->SetFont('Helvetica','B', 11);
                                $pdf->Cell(18,6,'Facture :',0,0,'L');

                                $pdf->SetFont('Helvetica','', 11);
                                $numfacture=$facture->getNumfacture();
                                $pdf->Cell(108,6,$numfacture.'/'.$mois,0,0,'L');

                                $pdf->SetFont('Helvetica','B', 11);
                                $pdf->Cell(30,6,utf8_decode("Date d'édition :"),0,0,'L');

                                $pdf->SetFont('Helvetica','', 11);
                                //$dateeditionfacture=$facture->getDateedition();
                                $pdf->Cell(30,6,$djomois,0,0);
                                //$pdf->MultiCell(88,4,'',0);
                                $pdf->Ln(6);
                            }elseif($tabrestPpg[$i]['nature']=='IN'){
                                $pdf->Ln(6);
                                $pdf->SetFont('Helvetica','B', 11);
                                $pdf->Cell(103,6,'INTERNATIONAL',0,0,'L');

                                $pdf->SetFont('Helvetica','', 11);
                                $pdf->Cell(88,6,'',0,0);
                                $pdf->Ln(6);
                            }elseif($tabrestPpg[$i]['nature']=='INE'){
                                $pdf->SetFont('Helvetica','B', 11);
                                $pdf->Cell(23,6,utf8_decode('Date'),1,0,'C');
                                $pdf->Cell(40,6,utf8_decode('Lieu de dépôt'),1,0,'C');
                                $pdf->Cell(40,6,utf8_decode('N° dépot'),1,0,'C');
                                $pdf->Cell(40,6,utf8_decode('Destination'),1,0,'C');
                                $pdf->Cell(20,6,utf8_decode('Poids/Kg'),1,0,'C');
                                $pdf->Cell(28,6,utf8_decode('Montant'),1,0,'C');
                                $pdf->SetFont('Helvetica','', 11);
                                $pdf->Ln(6);
                            }elseif($tabrestPpg[$i]['nature']=='EN'){
                                $pdf->Cell(23,6,utf8_decode($tabrestPpg[$i]['date']),1,0,'C');
                                $pdf->Cell(40,6,strtoupper(utf8_decode($tabrestPpg[$i]['agence'])),1,0,'L');

                                $pdf->Cell(40,6,strtoupper(utf8_decode($tabrestPpg[$i]['code'])),1,0,'C');

                                if($tabrestPpg[$i]['destfacture']!='etr'){
                                    $pdf->Cell(40,6,strtoupper(utf8_decode($tabrestPpg[$i]['destfacture'])),1,0,'L');
                                }else{
                                    $pdf->Cell(40,6,strtoupper(utf8_decode($tabrestPpg[$i]['destination'])),1,0,'L');
                                }


                                $pdf->Cell(20,6,utf8_decode($tabrestPpg[$i]['poids']),1,0,'C');

                                $pdf->Cell(28,6,utf8_decode(str_replace(","," ", number_format($tabrestPpg[$i]['montant']))),1,0,'R');
                                $pdf->Ln(6);
                            }elseif($tabrestPpg[$i]['nature']=='NA'){
                                $pdf->Ln(6);
                                $pdf->SetFont('Helvetica','B', 11);
                                $pdf->Cell(103,6,'NATIONAL',0,0,'L');

                                $pdf->SetFont('Helvetica','', 11);
                                $pdf->Cell(88,6,'',0,0);
                                $pdf->Ln(6);
                            }elseif($tabrestPpg[$i]['nature']=='NAE'){
                                $pdf->SetFont('Helvetica','B', 11);
                                $pdf->Cell(23,6,utf8_decode('Date'),1,0,'C');
                                $pdf->Cell(40,6,utf8_decode('Lieu de dépôt'),1,0,'C');
                                $pdf->Cell(40,6,utf8_decode('N° dépot'),1,0,'C');
                                $pdf->Cell(40,6,utf8_decode('Destination'),1,0,'C');
                                $pdf->Cell(20,6,utf8_decode('Poids/Kg'),1,0,'C');
                                $pdf->Cell(28,6,utf8_decode('Montant'),1,0,'C');
                                $pdf->SetFont('Helvetica','', 11);
                                $pdf->Ln(6);
                            }elseif($tabrestPpg[$i]['nature']=='TNI'){
                                $pdf->Cell(103,6,'',0,0,'C');

                                $pdf->SetFont('Helvetica','B', 11);
                                $pdf->Cell(60,6,'Total',1,0,'L');

                                $pdf->SetFont('Helvetica','', 11);
                                $pdf->Cell(28,6,utf8_decode(str_replace(","," ", number_format($tabrestPpg[$i]['montant']))),1,0,'R');
                                $pdf->Ln(6);
                            }elseif($tabrestPpg[$i]['nature']=='TNA'){
                                $pdf->Cell(103,6,'',0,0,'C');

                                $pdf->SetFont('Helvetica','B', 11);
                                $pdf->Cell(60,6,'Total',1,0,'L');

                                $pdf->SetFont('Helvetica','', 11);
                                $pdf->Cell(28,6,utf8_decode(str_replace(","," ", number_format($tabrestPpg[$i]['montant']))),1,0,'R');
                                $pdf->Ln(6);
                            }elseif($tabrestPpg[$i]['nature']=='TT'){
                                $pdf->Cell(103,6,'',0,0,'C');

                                $pdf->SetFont('Helvetica','B', 11);
                                $pdf->Cell(60,6,'Montant HT',1,0,'L');

                                $pdf->SetFont('Helvetica','', 11);
                                $pdf->Cell(28,6,utf8_decode(str_replace(","," ", number_format($tabrestPpg[$i]['montant']))),1,0,'R');
                                $pdf->Ln(6);
                            }elseif($tabrestPpg[$i]['nature']=='TVA'){
                                $pdf->Cell(103,6,'',0,0,'C');

                                $pdf->SetFont('Helvetica','B', 11);
                                $pdf->Cell(60,6,'TVA',1,0,'L');

                                $pdf->SetFont('Helvetica','', 11);
                                $pdf->Cell(28,6,utf8_decode(str_replace(","," ", number_format($tabrestPpg[$i]['montant']))),1,0,'R');
                                $pdf->Ln(6);
                            }elseif($tabrestPpg[$i]['nature']=='TTC'){
                                $pdf->Cell(103,6,'',0,0,'C');

                                $pdf->SetFont('Helvetica','B', 11);
                                $pdf->Cell(60,6,'Total TTC',1,0,'L');

                                $pdf->SetFont('Helvetica','', 11);
                                $pdf->Cell(28,6,utf8_decode(str_replace(","," ", number_format($tabrestPpg[$i]['montant']))),1,0,'R');
                                $pdf->Ln(6);
                            }elseif($tabrestPpg[$i]['nature']=='TAC'){


                                $pdf->SetFont('Helvetica','B', 11);
                                $pdf->Cell(111,6,utf8_decode('Arrétée la présente facture à la somme de :     '.str_replace(","," ", number_format($tabrestPpg[$i]['montant'])).' F'),0,0,'L');

                                $pdf->SetFont('Helvetica','', 11);
                                $pdf->Cell(80,6,'',0,0,'L');



                                $pdf->Ln(6);
                            }elseif($tabrestPpg[$i]['nature']=='TAL'){

                                $formatter = \NumberFormatter::create('fr_FR', \NumberFormatter::SPELLOUT);
                                $formatter->setAttribute(\NumberFormatter::FRACTION_DIGITS, 0);
                                $formatter->setAttribute(\NumberFormatter::ROUNDING_MODE, \NumberFormatter::ROUND_HALFUP);
                                $ttlettre= $formatter->format($tabrestPpg[$i]['montant']);   // un million cinq cent vingt-deux mille cinq cent trente
                                $pdf->SetFont('Helvetica','B', 11);
                                $pdf->Cell(191,6,utf8_decode('     '.strtoupper($ttlettre).' FCFA'),0,0,'L');
                                $pdf->SetFont('Helvetica','', 11);
                                $pdf->Ln(6);
                            }elseif($tabrestPpg[$i]['nature']=='CDT'){

                                $pdf->SetFont('Helvetica','U', 11);
                                $pdf->Cell(191,6,utf8_decode('CONDITIONS DE PAIEMENT :'),0,0,'L');
                                $pdf->SetFont('Helvetica','', 11);
                                $pdf->Ln(6);
                            }elseif($tabrestPpg[$i]['nature']=='DLM'){


                                $pdf->SetFont('Helvetica','', 11);
                                $pdf->Cell(56,6,utf8_decode('DATE LIMITE DE PAIEMENT :  '),0,0,'L');

                                $pdf->SetFont('Helvetica','B', 9);
                                $pdf->Cell(135,6,utf8_decode('20 jours après réception de la facture'),0,0,'L');

                                $pdf->SetFont('Helvetica','', 11);

                                $pdf->Ln(6);
                            }elseif($tabrestPpg[$i]['nature']=='VRM'){

                                $vrmfacture=$facture->getVirement();
                                $pdf->SetFont('Helvetica','', 11);
                                $pdf->Cell(24,6,utf8_decode('VIREMENT : '),0,0,'L');

                                $pdf->SetFont('Helvetica','B', 9);
                                $pdf->Cell(167,6,utf8_decode($vrmfacture),0,0,'L');

                                $pdf->SetFont('Helvetica','', 11);

                                $pdf->Ln(6);
                            }elseif($tabrestPpg[$i]['nature']=='ACQ'){

                                $pdf->SetFont('Helvetica','', 9);
                                $pdf->Cell(191,6,utf8_decode('Chèque à libeller au nom de EMS SENEGAL'),0,0,'L');
                                $pdf->SetFont('Helvetica','', 11);
                                $pdf->Ln(6);
                            }elseif($tabrestPpg[$i]['nature']=='LAU1'){


                                $pdf->SetFont('Helvetica','', 10);
                                $pdf->Cell(95,6,utf8_decode("LE DIRECTEUR DE L'ADMINISTRATION DES"),0,0,'L');

                                $pdf->SetFont('Helvetica','', 10);


                                if($autDG==null){
                                    $pdf->Cell(96,6,'LE DIRECTEUR GENERAL',0,0,'L');
                                }else{

                                    if($autDG->getTitre()=='DGTITULAIRE'){
                                        $pdf->Cell(96,6,'LE DIRECTEUR GENERAL',0,0,'L');
                                    }else{
                                        $pdf->Cell(96,6,'LE DIRECTEUR GENERAL PAR INTERIM',0,0,'L');
                                    }

                                }

                                $pdf->Ln(4);
                            }elseif($tabrestPpg[$i]['nature']=='LAU2'){


                                $pdf->SetFont('Helvetica','', 10);
                                if($autDAFC==null){
                                    $pdf->Cell(95,6,'LE DIRECTEUR GENERAL',0,0,'L');
                                }else{

                                    if($autDAFC->getTitre()=='DAFCTITULAIRE'){
                                        $pdf->Cell(95,6,'FINANCES ET DE LA COMPTABILITE',0,0,'L');
                                    }else{
                                        $pdf->Cell(95,6,'FINANCES ET DE LA COMPTABILITE PAR INTERIM',0,0,'L');
                                    }

                                }

                                $pdf->SetFont('Helvetica','', 10);
                                $pdf->Cell(96,6,'',0,0,'L');

                                $pdf->Ln(6);
                            }elseif($tabrestPpg[$i]['nature']=='LAU3'){


                                $pdf->SetFont('Helvetica','', 10);
                                if($autDAFC!=null){
                                    $nomdaf=$autDAFC->getNom();
                                    $pdf->Cell(95,6,utf8_decode(strtoupper($nomdaf)),0,0,'L');
                                }else{
                                    $pdf->Cell(95,6,'',0,0,'L');
                                }

                                $pdf->SetFont('Helvetica','', 10);
                                if($autDG!=null){
                                    $nomdg=$autDG->getNom();
                                    $pdf->Cell(96,6,utf8_decode(strtoupper($nomdg)),0,0,'L');
                                }else{
                                    $pdf->Cell(96,6,'',0,0,'L');
                                }

                                $pdf->Ln(6);
                            }



                        }

//eeee
                        for($i=0; $i<39-count($tabrestPpg)-$modtabEnr; $i++){



                            //$pdf->SetFont('Helvetica','', 11);
                            $pdf->Cell(191,6,'',0,0);
                            $pdf->Ln(6);

                        }

                    }else{ //if($modtabEnr==20 or $modtabEnr==21)


                        $pdf->Cell(191,6,'',0,0);
                        $pdf->Ln(6);
                        $pdf->Cell(191,6,'',0,0);
                        $pdf->Ln(6);
                        $pdf->Cell(191,6,'',0,0);
                        $pdf->Ln(6);
                        $pdf->Cell(191,6,'',0,0);
                        $pdf->Ln(6);


                        for($i=count($tabrestEnr)-$modtabEnr; $i<count($tabrestEnr)-4; $i++){   //count($tabrestEnr);

                            if($i!=0 and $i%34==0){




                                // $nbrp=$nbrp+1;

                                //Footer page
                                $PageNo= $PageNo+1;
                                $pdf->Ln(6);
                                $pdf->SetFont('Helvetica','I',9);
                                $infopieds1=utf8_decode('Domaine SODIDA, lot n° 49 Dakar Tél. (221) 869.01.01 / Fax : 869.01.02 BP: 15 305 Email : ems@ems.sn');
                                $infopieds2='RC SN DK 2008 M19490-NINEA 002591536 2G3';
                                //$infopieds=$infopieds1."\n".$infopieds2;
                                $pdf->Cell(163,6,$infopieds1,'TR',0,'C');
                                $pdf->SetFont('Helvetica','B',9);
                                $pdf->Cell(28,6,'Page '.  $PageNo." sur ".$nbpage,0,0,'C');
                                $pdf->Ln(4);
                                $pdf->SetFont('Helvetica','I',9);
                                $pdf->Cell(163,6,$infopieds2,'R',0,'C');
                                $pdf->Cell(28,6,'',0,0,'C');

                                //New page
                                $pdf->AddPage();
                                $pdf->SetFont('Helvetica','', 11);
                                //Header page
                                $pdf->Image("img/logo-ems.jpg",1,null,50,12);
                                $pdf->Ln(5);
                            }



                            if($tabrestEnr[$i]['nature']=='LV'){


                                //$pdf->SetFont('Helvetica','', 11);
                                $pdf->Cell(191,6,'',0,0);
                                $pdf->Ln(6);
                            }elseif($tabrestEnr[$i]['nature']=='IN'){
                                $pdf->Ln(6);
                                $pdf->SetFont('Helvetica','B', 11);
                                $pdf->Cell(103,6,'INTERNATIONAL',0,0,'L');

                                $pdf->SetFont('Helvetica','', 11);
                                $pdf->Cell(88,6,'',0,0);
                                $pdf->Ln(6);
                            }elseif($tabrestEnr[$i]['nature']=='INE'){
                                $pdf->SetFont('Helvetica','B', 11);
                                $pdf->Cell(23,6,utf8_decode('Date'),1,0,'C');
                                $pdf->Cell(40,6,utf8_decode('Lieu de dépôt'),1,0,'C');
                                $pdf->Cell(40,6,utf8_decode('N° dépot'),1,0,'C');
                                $pdf->Cell(40,6,utf8_decode('Destination'),1,0,'C');
                                $pdf->Cell(20,6,utf8_decode('Poids/Kg'),1,0,'C');
                                $pdf->Cell(28,6,utf8_decode('Montant'),1,0,'C');
                                $pdf->SetFont('Helvetica','', 11);
                                $pdf->Ln(6);
                            }elseif($tabrestEnr[$i]['nature']=='EN'){
                                $pdf->Cell(23,6,utf8_decode($tabrestEnr[$i]['date']),1,0,'C');
                                $pdf->Cell(40,6,strtoupper(utf8_decode($tabrestEnr[$i]['agence'])),1,0,'L');

                                $pdf->Cell(40,6,strtoupper(utf8_decode($tabrestEnr[$i]['code'])),1,0,'C');

                                if($tabrestEnr[$i]['destfacture']!='etr'){
                                    $pdf->Cell(40,6,strtoupper(utf8_decode($tabrestEnr[$i]['destfacture'])),1,0,'L');
                                }else{
                                    $pdf->Cell(40,6,strtoupper(utf8_decode($tabrestEnr[$i]['destination'])),1,0,'L');
                                }


                                $pdf->Cell(20,6,utf8_decode($tabrestEnr[$i]['poids']),1,0,'C');

                                $pdf->Cell(28,6,utf8_decode(str_replace(","," ", number_format($tabrestEnr[$i]['montant']))),1,0,'R');
                                $pdf->Ln(6);
                            }elseif($tabrestEnr[$i]['nature']=='NA'){
                                $pdf->Ln(6);
                                $pdf->SetFont('Helvetica','B', 11);
                                $pdf->Cell(103,6,'NATIONAL',0,0,'L');

                                $pdf->SetFont('Helvetica','', 11);
                                $pdf->Cell(88,6,'',0,0);
                                $pdf->Ln(6);
                            }elseif($tabrestEnr[$i]['nature']=='NAE'){
                                $pdf->SetFont('Helvetica','B', 11);
                                $pdf->Cell(23,6,utf8_decode('Date'),1,0,'C');
                                $pdf->Cell(40,6,utf8_decode('Lieu de dépôt'),1,0,'C');
                                $pdf->Cell(40,6,utf8_decode('N° dépot'),1,0,'C');
                                $pdf->Cell(40,6,utf8_decode('Destination'),1,0,'C');
                                $pdf->Cell(20,6,utf8_decode('Poids/Kg'),1,0,'C');
                                $pdf->Cell(28,6,utf8_decode('Montant'),1,0,'C');
                                $pdf->SetFont('Helvetica','', 11);
                                $pdf->Ln(6);
                            }elseif($tabrestEnr[$i]['nature']=='TNI'){
                                $pdf->Cell(103,6,'',0,0,'C');

                                $pdf->SetFont('Helvetica','B', 11);
                                $pdf->Cell(60,6,'Total',1,0,'L');

                                $pdf->SetFont('Helvetica','', 11);
                                $pdf->Cell(28,6,utf8_decode(str_replace(","," ", number_format($tabrestEnr[$i]['montant']))),1,0,'R');
                                $pdf->Ln(6);
                            }elseif($tabrestEnr[$i]['nature']=='TNA'){
                                $pdf->Cell(103,6,'',0,0,'C');

                                $pdf->SetFont('Helvetica','B', 11);
                                $pdf->Cell(60,6,'Total',1,0,'L');

                                $pdf->SetFont('Helvetica','', 11);
                                $pdf->Cell(28,6,utf8_decode(str_replace(","," ", number_format($tabrestEnr[$i]['montant']))),1,0,'R');
                                $pdf->Ln(6);
                            }elseif($tabrestEnr[$i]['nature']=='TT'){
                                $pdf->Cell(103,6,'',0,0,'C');

                                $pdf->SetFont('Helvetica','B', 11);
                                $pdf->Cell(60,6,'Montant HT',1,0,'L');

                                $pdf->SetFont('Helvetica','', 11);
                                $pdf->Cell(28,6,utf8_decode(str_replace(","," ", number_format($tabrestEnr[$i]['montant']))),1,0,'R');
                                $pdf->Ln(6);
                            }elseif($tabrestEnr[$i]['nature']=='TVA'){
                                $pdf->Cell(103,6,'',0,0,'C');

                                $pdf->SetFont('Helvetica','B', 11);
                                $pdf->Cell(60,6,'TVA',1,0,'L');

                                $pdf->SetFont('Helvetica','', 11);
                                $pdf->Cell(28,6,utf8_decode(str_replace(","," ", number_format($tabrestEnr[$i]['montant']))),1,0,'R');
                                $pdf->Ln(6);
                            }elseif($tabrestEnr[$i]['nature']=='TTC'){
                                $pdf->Cell(103,6,'',0,0,'C');

                                $pdf->SetFont('Helvetica','B', 11);
                                $pdf->Cell(60,6,'Total TTC',1,0,'L');

                                $pdf->SetFont('Helvetica','', 11);
                                $pdf->Cell(28,6,utf8_decode(str_replace(","," ", number_format($tabrestEnr[$i]['montant']))),1,0,'R');
                                $pdf->Ln(6);
                            }



                        }
//zzz

//var_dump(count($tabrestEnr));die();
                        for($i=0; $i<42-$modtabEnr; $i++){



                            //$pdf->SetFont('Helvetica','', 11);
                            $pdf->Cell(191,6,'',0,0);
                            $pdf->Ln(6);

                        }

                        //Footer page
                        $PageNo= $PageNo+1;
                        $pdf->Ln(6);
                        $pdf->SetFont('Helvetica','I',9);
                        $infopieds1=utf8_decode('Domaine SODIDA, lot n° 49 Dakar Tél. (221) 869.01.01 / Fax : 869.01.02 BP: 15 305 Email : ems@ems.sn');
                        $infopieds2='RC SN DK 2008 M19490-NINEA 002591536 2G3';
                        //$infopieds=$infopieds1."\n".$infopieds2;
                        $pdf->Cell(163,6,$infopieds1,'TR',0,'C');
                        $pdf->SetFont('Helvetica','B',9);
                        $pdf->Cell(28,6,'Page '.  $PageNo." sur ".$nbpage,0,0,'C');
                        $pdf->Ln(4);
                        $pdf->SetFont('Helvetica','I',9);
                        $pdf->Cell(163,6,$infopieds2,'R',0,'C');
                        $pdf->Cell(28,6,'',0,0,'C');
                        // $pdf->MultiCell(163,12,$infopieds,'TR','C');

                        $pdf->AddPage();
                        $pdf->SetFont('Helvetica','', 11);
                        //Header page
                        $pdf->Image("img/logo-ems.jpg",1,null,50,12);
                        $pdf->Ln(5);



//Ligne Total
                        $pdf->Cell(103,6,'',0,0,'C');

                        $pdf->SetFont('Helvetica','B', 11);
                        $pdf->Cell(60,6,'Total',1,0,'L');

                        $pdf->SetFont('Helvetica','', 11);
                        $pdf->Cell(28,6,utf8_decode(str_replace(","," ", number_format($tabrestEnr[count($tabrestEnr)-4]['montant']))),1,0,'R');
                        $pdf->Ln(6);

                        //Ligne Montant HT
                        $pdf->Cell(103,6,'',0,0,'C');

                        $pdf->SetFont('Helvetica','B', 11);
                        $pdf->Cell(60,6,'Montant HT',1,0,'L');

                        $pdf->SetFont('Helvetica','', 11);
                        $pdf->Cell(28,6,utf8_decode(str_replace(","," ", number_format($tabrestEnr[count($tabrestEnr)-3]['montant']))),1,0,'R');
                        $pdf->Ln(6);

                        //Ligne TVA
                        $pdf->Cell(103,6,'',0,0,'C');

                        $pdf->SetFont('Helvetica','B', 11);
                        $pdf->Cell(60,6,'TVA',1,0,'L');

                        $pdf->SetFont('Helvetica','', 11);
                        $pdf->Cell(28,6,utf8_decode(str_replace(","," ", number_format($tabrestEnr[count($tabrestEnr)-2]['montant']))),1,0,'R');
                        $pdf->Ln(6);
                        //Ligne TTC
                        $pdf->Cell(103,6,'',0,0,'C');
                        $pdf->SetFont('Helvetica','B', 11);
                        $pdf->Cell(60,6,'Total TTC',1,0,'L');

                        $pdf->SetFont('Helvetica','', 11);
                        $pdf->Cell(28,6,utf8_decode(str_replace(","," ", number_format($tabrestEnr[count($tabrestEnr)-1]['montant']))),1,0,'R');
                        $pdf->Ln(6);

                        for($i=0; $i<count($tabrestPpg); $i++){

                            if($i!=0 and $i%34==0){
                                // $nbrp=$nbrp+1;

                                //Footer page
                                $PageNo= $PageNo+1;
                                $pdf->Ln(6);
                                $pdf->SetFont('Helvetica','I',9);
                                $infopieds1=utf8_decode('Domaine SODIDA, lot n° 49 Dakar Tél. (221) 869.01.01 / Fax : 869.01.02 BP: 15 305 Email : ems@ems.sn');
                                $infopieds2='RC SN DK 2008 M19490-NINEA 002591536 2G3';
                                //$infopieds=$infopieds1."\n".$infopieds2;
                                $pdf->Cell(163,6,$infopieds1,'TR',0,'C');
                                $pdf->SetFont('Helvetica','B',9);
                                $pdf->Cell(28,6,'Page '.  $PageNo." sur ".$nbpage,0,0,'C');
                                $pdf->Ln(4);
                                $pdf->SetFont('Helvetica','I',9);
                                $pdf->Cell(163,6,$infopieds2,'R',0,'C');
                                $pdf->Cell(28,6,'',0,0,'C');

                                //New page
                                $pdf->AddPage();
                                $pdf->SetFont('Helvetica','', 11);
                                //Header page
                                $pdf->Image("img/logo-ems.jpg",1,null,50,12);
                                $pdf->Ln(5);
                            }



                            if($tabrestPpg[$i]['nature']=='PR'){
                                $pdf->Cell(103,6,'',0,0,'C');
                                $tetx='Période facturée :';
                                $pdf->SetFont('Helvetica','B', 11);
                                $pdf->Cell(58,6,utf8_decode($tetx),0,0,'R');

                                $pdf->SetFont('Helvetica','', 11);
                                $pdf->Cell(30,6,$tabrestPpg[$i]['code'],0,0,'L');
                                $pdf->Ln(6);
                            }elseif($tabrestPpg[$i]['nature']=='NAB'){

                                $pdf->SetFont('Helvetica','B', 11);
                                $str= utf8_encode($this->removeAccents($tabrestPpg[$i]['code']));
                                $pdf->MultiCell(103,6,strtoupper($str),0,'L');

                                $pdf->SetFont('Helvetica','', 11);
                                $pdf->MultiCell(88,6,'',0);
                            }elseif($tabrestPpg[$i]['nature']=='AAB'){

                                $pdf->SetFont('Helvetica','B', 11);
                                //$pdf->Cell(103,6,utf8_decode($tabrestPpg[$i]['code']),1,0,'L');

                                $str= utf8_encode($this->removeAccents($tabrestPpg[$i]['code']));
                                $pdf->MultiCell(103,6,strtoupper($str),0,'L');

                                $pdf->SetFont('Helvetica','', 11);
                                //$pdf->Cell(88,6,'',0,0);
                                $pdf->MultiCell(88,6,'',0);
                            }elseif($tabrestPpg[$i]['nature']=='LV'){


                                //$pdf->SetFont('Helvetica','', 11);
                                $pdf->Cell(191,6,'',0,0);
                                $pdf->Ln(6);
                            }elseif($tabrestPpg[$i]['nature']=='NFA'){

                                $pdf->SetFont('Helvetica','B', 11);
                                $pdf->Cell(18,6,'Facture :',0,0,'L');

                                $pdf->SetFont('Helvetica','', 11);
                                $numfacture=$facture->getNumfacture();
                                $pdf->Cell(108,6,$numfacture.'/'.$mois,0,0,'L');

                                $pdf->SetFont('Helvetica','B', 11);
                                $pdf->Cell(30,6,utf8_decode("Date d'édition :"),0,0,'L');

                                $pdf->SetFont('Helvetica','', 11);
                                //$dateeditionfacture=$facture->getDateedition();
                                $pdf->Cell(30,6,$djomois,0,0);
                                //$pdf->MultiCell(88,4,'',0);
                                $pdf->Ln(6);
                            }elseif($tabrestPpg[$i]['nature']=='IN'){
                                $pdf->Ln(6);
                                $pdf->SetFont('Helvetica','B', 11);
                                $pdf->Cell(103,6,'INTERNATIONAL',0,0,'L');

                                $pdf->SetFont('Helvetica','', 11);
                                $pdf->Cell(88,6,'',0,0);
                                $pdf->Ln(6);
                            }elseif($tabrestPpg[$i]['nature']=='INE'){
                                $pdf->SetFont('Helvetica','B', 11);
                                $pdf->Cell(23,6,utf8_decode('Date'),1,0,'C');
                                $pdf->Cell(40,6,utf8_decode('Lieu de dépôt'),1,0,'C');
                                $pdf->Cell(40,6,utf8_decode('N° dépot'),1,0,'C');
                                $pdf->Cell(40,6,utf8_decode('Destination'),1,0,'C');
                                $pdf->Cell(20,6,utf8_decode('Poids/Kg'),1,0,'C');
                                $pdf->Cell(28,6,utf8_decode('Montant'),1,0,'C');
                                $pdf->SetFont('Helvetica','', 11);
                                $pdf->Ln(6);
                            }elseif($tabrestPpg[$i]['nature']=='EN'){
                                $pdf->Cell(23,6,utf8_decode($tabrestPpg[$i]['date']),1,0,'C');
                                $pdf->Cell(40,6,strtoupper(utf8_decode($tabrestPpg[$i]['agence'])),1,0,'L');

                                $pdf->Cell(40,6,strtoupper(utf8_decode($tabrestPpg[$i]['code'])),1,0,'C');

                                if($tabrestPpg[$i]['destfacture']!='etr'){
                                    $pdf->Cell(40,6,strtoupper(utf8_decode($tabrestPpg[$i]['destfacture'])),1,0,'L');
                                }else{
                                    $pdf->Cell(40,6,strtoupper(utf8_decode($tabrestPpg[$i]['destination'])),1,0,'L');
                                }


                                $pdf->Cell(20,6,utf8_decode($tabrestPpg[$i]['poids']),1,0,'C');

                                $pdf->Cell(28,6,utf8_decode(str_replace(","," ", number_format($tabrestPpg[$i]['montant']))),1,0,'R');
                                $pdf->Ln(6);
                            }elseif($tabrestPpg[$i]['nature']=='NA'){
                                $pdf->Ln(6);
                                $pdf->SetFont('Helvetica','B', 11);
                                $pdf->Cell(103,6,'NATIONAL',0,0,'L');

                                $pdf->SetFont('Helvetica','', 11);
                                $pdf->Cell(88,6,'',0,0);
                                $pdf->Ln(6);
                            }elseif($tabrestPpg[$i]['nature']=='NAE'){
                                $pdf->SetFont('Helvetica','B', 11);
                                $pdf->Cell(23,6,utf8_decode('Date'),1,0,'C');
                                $pdf->Cell(40,6,utf8_decode('Lieu de dépôt'),1,0,'C');
                                $pdf->Cell(40,6,utf8_decode('N° dépot'),1,0,'C');
                                $pdf->Cell(40,6,utf8_decode('Destination'),1,0,'C');
                                $pdf->Cell(20,6,utf8_decode('Poids/Kg'),1,0,'C');
                                $pdf->Cell(28,6,utf8_decode('Montant'),1,0,'C');
                                $pdf->SetFont('Helvetica','', 11);
                                $pdf->Ln(6);
                            }elseif($tabrestPpg[$i]['nature']=='TNI'){
                                $pdf->Cell(103,6,'',0,0,'C');

                                $pdf->SetFont('Helvetica','B', 11);
                                $pdf->Cell(60,6,'Total',1,0,'L');

                                $pdf->SetFont('Helvetica','', 11);
                                $pdf->Cell(28,6,utf8_decode(str_replace(","," ", number_format($tabrestPpg[$i]['montant']))),1,0,'R');
                                $pdf->Ln(6);
                            }elseif($tabrestPpg[$i]['nature']=='TNA'){
                                $pdf->Cell(103,6,'',0,0,'C');

                                $pdf->SetFont('Helvetica','B', 11);
                                $pdf->Cell(60,6,'Total',1,0,'L');

                                $pdf->SetFont('Helvetica','', 11);
                                $pdf->Cell(28,6,utf8_decode(str_replace(","," ", number_format($tabrestPpg[$i]['montant']))),1,0,'R');
                                $pdf->Ln(6);
                            }elseif($tabrestPpg[$i]['nature']=='TT'){
                                $pdf->Cell(103,6,'',0,0,'C');

                                $pdf->SetFont('Helvetica','B', 11);
                                $pdf->Cell(60,6,'Montant HT',1,0,'L');

                                $pdf->SetFont('Helvetica','', 11);
                                $pdf->Cell(28,6,utf8_decode(str_replace(","," ", number_format($tabrestPpg[$i]['montant']))),1,0,'R');
                                $pdf->Ln(6);
                            }elseif($tabrestPpg[$i]['nature']=='TVA'){
                                $pdf->Cell(103,6,'',0,0,'C');

                                $pdf->SetFont('Helvetica','B', 11);
                                $pdf->Cell(60,6,'TVA',1,0,'L');

                                $pdf->SetFont('Helvetica','', 11);
                                $pdf->Cell(28,6,utf8_decode(str_replace(","," ", number_format($tabrestPpg[$i]['montant']))),1,0,'R');
                                $pdf->Ln(6);
                            }elseif($tabrestPpg[$i]['nature']=='TTC'){
                                $pdf->Cell(103,6,'',0,0,'C');

                                $pdf->SetFont('Helvetica','B', 11);
                                $pdf->Cell(60,6,'Total TTC',1,0,'L');

                                $pdf->SetFont('Helvetica','', 11);
                                $pdf->Cell(28,6,utf8_decode(str_replace(","," ", number_format($tabrestPpg[$i]['montant']))),1,0,'R');
                                $pdf->Ln(6);
                            }elseif($tabrestPpg[$i]['nature']=='TAC'){


                                $pdf->SetFont('Helvetica','B', 11);
                                $pdf->Cell(111,6,utf8_decode('Arrétée la présente facture à la somme de :     '.str_replace(","," ", number_format($tabrestPpg[$i]['montant'])).' F'),0,0,'L');

                                $pdf->SetFont('Helvetica','', 11);
                                $pdf->Cell(80,6,'',0,0,'L');



                                $pdf->Ln(6);
                            }elseif($tabrestPpg[$i]['nature']=='TAL'){

                                $formatter = \NumberFormatter::create('fr_FR', \NumberFormatter::SPELLOUT);
                                $formatter->setAttribute(\NumberFormatter::FRACTION_DIGITS, 0);
                                $formatter->setAttribute(\NumberFormatter::ROUNDING_MODE, \NumberFormatter::ROUND_HALFUP);
                                $ttlettre= $formatter->format($tabrestPpg[$i]['montant']);   // un million cinq cent vingt-deux mille cinq cent trente
                                $pdf->SetFont('Helvetica','B', 11);
                                $pdf->Cell(191,6,utf8_decode('     '.strtoupper($ttlettre).' FCFA'),0,0,'L');
                                $pdf->SetFont('Helvetica','', 11);
                                $pdf->Ln(6);
                            }elseif($tabrestPpg[$i]['nature']=='CDT'){

                                $pdf->SetFont('Helvetica','U', 11);
                                $pdf->Cell(191,6,utf8_decode('CONDITIONS DE PAIEMENT :'),0,0,'L');
                                $pdf->SetFont('Helvetica','', 11);
                                $pdf->Ln(6);
                            }elseif($tabrestPpg[$i]['nature']=='DLM'){


                                $pdf->SetFont('Helvetica','', 11);
                                $pdf->Cell(56,6,utf8_decode('DATE LIMITE DE PAIEMENT :  '),0,0,'L');

                                $pdf->SetFont('Helvetica','B', 9);
                                $pdf->Cell(135,6,utf8_decode('20 jours après réception de la facture'),0,0,'L');

                                $pdf->SetFont('Helvetica','', 11);

                                $pdf->Ln(6);
                            }elseif($tabrestPpg[$i]['nature']=='VRM'){

                                $vrmfacture=$facture->getVirement();
                                $pdf->SetFont('Helvetica','', 11);
                                $pdf->Cell(24,6,utf8_decode('VIREMENT : '),0,0,'L');

                                $pdf->SetFont('Helvetica','B', 9);
                                $pdf->Cell(167,6,utf8_decode($vrmfacture),0,0,'L');

                                $pdf->SetFont('Helvetica','', 11);

                                $pdf->Ln(6);
                            }elseif($tabrestPpg[$i]['nature']=='ACQ'){

                                $pdf->SetFont('Helvetica','', 9);
                                $pdf->Cell(191,6,utf8_decode('Chèque à libeller au nom de EMS SENEGAL'),0,0,'L');
                                $pdf->SetFont('Helvetica','', 11);
                                $pdf->Ln(6);
                            }elseif($tabrestPpg[$i]['nature']=='LAU1'){


                                $pdf->SetFont('Helvetica','', 10);
                                $pdf->Cell(95,6,utf8_decode("LE DIRECTEUR DE L'ADMINISTRATION DES"),0,0,'L');

                                $pdf->SetFont('Helvetica','', 10);


                                if($autDG==null){
                                    $pdf->Cell(96,6,'LE DIRECTEUR GENERAL',0,0,'L');
                                }else{

                                    if($autDG->getTitre()=='DGTITULAIRE'){
                                        $pdf->Cell(96,6,'LE DIRECTEUR GENERAL',0,0,'L');
                                    }else{
                                        $pdf->Cell(96,6,'LE DIRECTEUR GENERAL PAR INTERIM',0,0,'L');
                                    }

                                }

                                $pdf->Ln(4);
                            }elseif($tabrestPpg[$i]['nature']=='LAU2'){


                                $pdf->SetFont('Helvetica','', 10);
                                if($autDAFC==null){
                                    $pdf->Cell(95,6,'LE DIRECTEUR GENERAL',0,0,'L');
                                }else{

                                    if($autDAFC->getTitre()=='DAFCTITULAIRE'){
                                        $pdf->Cell(95,6,'FINANCES ET DE LA COMPTABILITE',0,0,'L');
                                    }else{
                                        $pdf->Cell(95,6,'FINANCES ET DE LA COMPTABILITE PAR INTERIM',0,0,'L');
                                    }

                                }

                                $pdf->SetFont('Helvetica','', 10);
                                $pdf->Cell(96,6,'',0,0,'L');

                                $pdf->Ln(6);
                            }elseif($tabrestPpg[$i]['nature']=='LAU3'){


                                $pdf->SetFont('Helvetica','', 10);
                                if($autDAFC!=null){
                                    $nomdaf=$autDAFC->getNom();
                                    $pdf->Cell(95,6,utf8_decode(strtoupper($nomdaf)),0,0,'L');
                                }else{
                                    $pdf->Cell(95,6,'',0,0,'L');
                                }

                                $pdf->SetFont('Helvetica','', 10);
                                if($autDG!=null){
                                    $nomdg=$autDG->getNom();
                                    $pdf->Cell(96,6,utf8_decode(strtoupper($nomdg)),0,0,'L');
                                }else{
                                    $pdf->Cell(96,6,'',0,0,'L');
                                }

                                $pdf->Ln(6);
                            }



                        }

                        for($i=0; $i<35-count($tabrestPpg); $i++){



                            //$pdf->SetFont('Helvetica','', 11);
                            $pdf->Cell(191,6,'',0,0);
                            $pdf->Ln(6);

                        }
                        // var_dump("ss");die();

                    }//else{}


                    //count($tab);
                    //var_dump(count($tabrestPpg));die();
                }


                //Footer page
            $PageNo= $PageNo+1;
                $pdf->Ln(6);
                $pdf->SetFont('Helvetica','I',9);
                $infopieds1=utf8_decode('Domaine SODIDA, lot n° 49 Dakar Tél. (221) 869.01.01 / Fax : 869.01.02 BP: 15 305 Email : ems@ems.sn');
                $infopieds2='RC SN DK 2008 M19490-NINEA 002591536 2G3';
                //$infopieds=$infopieds1."\n".$infopieds2;
                $pdf->Cell(163,6,$infopieds1,'TR',0,'C');
                $pdf->SetFont('Helvetica','B',9);
                $pdf->Cell(28,6,'Page '.$PageNo." sur ".$nbpage,0,0,'C');
                $pdf->Ln(4);
                $pdf->SetFont('Helvetica','I',9);
                $pdf->Cell(163,6,$infopieds2,'R',0,'C');
                $pdf->Cell(28,6,'',0,0,'C');
                // $pdf->MultiCell(163,12,$infopieds,'TR','C');



                //var_dump($envoisin);die();



        }


 //var_dump($nbbq);die();
        return new Response($pdf->Output(), 200, array(
            'Content-Type' => 'application/pdf'));




//var_dump("sss");die();

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


    function removeAccents($str) {
        $a = array('À', 'Á', 'Â', 'Ã', 'Ä', 'Å', 'Æ', 'Ç', 'È', 'É', 'Ê', 'Ë', 'Ì', 'Í', 'Î', 'Ï', 'Ð', 'Ñ', 'Ò', 'Ó', 'Ô', 'Õ', 'Ö', 'Ø', 'Ù', 'Ú', 'Û', 'Ü', 'Ý', 'ß', 'à', 'á', 'â', 'ã', 'ä', 'å', 'æ', 'ç', 'è', 'é', 'ê', 'ë', 'ì', 'í', 'î', 'ï', 'ñ', 'ò', 'ó', 'ô', 'õ', 'ö', 'ø', 'ù', 'ú', 'û', 'ü', 'ý', 'ÿ', 'Ā', 'ā', 'Ă', 'ă', 'Ą', 'ą', 'Ć', 'ć', 'Ĉ', 'ĉ', 'Ċ', 'ċ', 'Č', 'č', 'Ď', 'ď', 'Đ', 'đ', 'Ē', 'ē', 'Ĕ', 'ĕ', 'Ė', 'ė', 'Ę', 'ę', 'Ě', 'ě', 'Ĝ', 'ĝ', 'Ğ', 'ğ', 'Ġ', 'ġ', 'Ģ', 'ģ', 'Ĥ', 'ĥ', 'Ħ', 'ħ', 'Ĩ', 'ĩ', 'Ī', 'ī', 'Ĭ', 'ĭ', 'Į', 'į', 'İ', 'ı', 'Ĳ', 'ĳ', 'Ĵ', 'ĵ', 'Ķ', 'ķ', 'Ĺ', 'ĺ', 'Ļ', 'ļ', 'Ľ', 'ľ', 'Ŀ', 'ŀ', 'Ł', 'ł', 'Ń', 'ń', 'Ņ', 'ņ', 'Ň', 'ň', 'ŉ', 'Ō', 'ō', 'Ŏ', 'ŏ', 'Ő', 'ő', 'Œ', 'œ', 'Ŕ', 'ŕ', 'Ŗ', 'ŗ', 'Ř', 'ř', 'Ś', 'ś', 'Ŝ', 'ŝ', 'Ş', 'ş', 'Š', 'š', 'Ţ', 'ţ', 'Ť', 'ť', 'Ŧ', 'ŧ', 'Ũ', 'ũ', 'Ū', 'ū', 'Ŭ', 'ŭ', 'Ů', 'ů', 'Ű', 'ű', 'Ų', 'ų', 'Ŵ', 'ŵ', 'Ŷ', 'ŷ', 'Ÿ', 'Ź', 'ź', 'Ż', 'ż', 'Ž', 'ž', 'ſ', 'ƒ', 'Ơ', 'ơ', 'Ư', 'ư', 'Ǎ', 'ǎ', 'Ǐ', 'ǐ', 'Ǒ', 'ǒ', 'Ǔ', 'ǔ', 'Ǖ', 'ǖ', 'Ǘ', 'ǘ', 'Ǚ', 'ǚ', 'Ǜ', 'ǜ', 'Ǻ', 'ǻ', 'Ǽ', 'ǽ', 'Ǿ', 'ǿ', 'Ά', 'ά', 'Έ', 'έ', 'Ό', 'ό', 'Ώ', 'ώ', 'Ί', 'ί', 'ϊ', 'ΐ', 'Ύ', 'ύ', 'ϋ', 'ΰ', 'Ή', 'ή');
        $b = array('A', 'A', 'A', 'A', 'A', 'A', 'AE', 'C', 'E', 'E', 'E', 'E', 'I', 'I', 'I', 'I', 'D', 'N', 'O', 'O', 'O', 'O', 'O', 'O', 'U', 'U', 'U', 'U', 'Y', 's', 'a', 'a', 'a', 'a', 'a', 'a', 'ae', 'c', 'e', 'e', 'e', 'e', 'i', 'i', 'i', 'i', 'n', 'o', 'o', 'o', 'o', 'o', 'o', 'u', 'u', 'u', 'u', 'y', 'y', 'A', 'a', 'A', 'a', 'A', 'a', 'C', 'c', 'C', 'c', 'C', 'c', 'C', 'c', 'D', 'd', 'D', 'd', 'E', 'e', 'E', 'e', 'E', 'e', 'E', 'e', 'E', 'e', 'G', 'g', 'G', 'g', 'G', 'g', 'G', 'g', 'H', 'h', 'H', 'h', 'I', 'i', 'I', 'i', 'I', 'i', 'I', 'i', 'I', 'i', 'IJ', 'ij', 'J', 'j', 'K', 'k', 'L', 'l', 'L', 'l', 'L', 'l', 'L', 'l', 'l', 'l', 'N', 'n', 'N', 'n', 'N', 'n', 'n', 'O', 'o', 'O', 'o', 'O', 'o', 'OE', 'oe', 'R', 'r', 'R', 'r', 'R', 'r', 'S', 's', 'S', 's', 'S', 's', 'S', 's', 'T', 't', 'T', 't', 'T', 't', 'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u', 'W', 'w', 'Y', 'y', 'Y', 'Z', 'z', 'Z', 'z', 'Z', 'z', 's', 'f', 'O', 'o', 'U', 'u', 'A', 'a', 'I', 'i', 'O', 'o', 'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u', 'A', 'a', 'AE', 'ae', 'O', 'o', 'Α', 'α', 'Ε', 'ε', 'Ο', 'ο', 'Ω', 'ω', 'Ι', 'ι', 'ι', 'ι', 'Υ', 'υ', 'υ', 'υ', 'Η', 'η');
        return str_replace($a, $b, $str);
    }

}
