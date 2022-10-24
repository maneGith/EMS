<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Zone;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;use Symfony\Component\HttpFoundation\Request;

/**
 * Zone controller.
 *
 * @Route("admin/tarifs")
 */
class ZoneController extends Controller
{
    /**
     * Lists all zone entities.
     *
     * @Route("/", name="zone_index")
     * @Method("GET")
     */
    public function indexAction(Request $request)
    {

        $zone = new Zone();
        $form = $this->createForm('AppBundle\Form\ZoneType', $zone);
        $form->handleRequest($request);

        $em = $this->getDoctrine()->getManager();

        $zones = $em->getRepository('AppBundle:Zone')->findAll();

        return $this->render('zone/index.html.twig', array(
            'zones' => $zones,
            'form' => $form->createView(),
        ));
    }

    /**
     * Creates a new zone entity.
     *
     * @Route("/new", name="zone_new")
     * @Method({"GET", "POST"})
     */
    public function newAction(Request $request)
    {
        $zone = new Zone();
        $form = $this->createForm('AppBundle\Form\ZoneType', $zone);
        $form->handleRequest($request);

        $em = $this->getDoctrine()->getManager();
        $zones = $em->getRepository('AppBundle:Zone')->findAll();

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($zone);
            $em->flush();

            return $this->redirectToRoute('zone_edit', array('id' => $zone->getId()));
        }

        return $this->render('zone/new.html.twig', array(
            'zone' => $zone,
            'form' => $form->createView(),
            'zones' => $zones,
        ));
    }


    /**
     * Displays a form to edit an existing zone entity.
     *
     * @Route("/{id}/edit", name="zone_edit")
     * @Method({"GET", "POST"})
     */
    public function editAction(Request $request, Zone $zone)
    {


       $pays=$this->paysZone($zone->getId());

        $deleteForm = $this->createDeleteForm($zone);
        $editForm = $this->createForm('AppBundle\Form\ZoneType', $zone);
        $editForm->handleRequest($request);

        if ($editForm->isSubmitted() && $editForm->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('zone_edit', array('id' => $zone->getId()));
        }

        return $this->render('zone/edit.html.twig', array(
            'zone' => $zone,
            'pays' => $pays,
            'edit_form' => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Deletes a zone entity.
     *
     * @Route("/{id}", name="zone_delete")
     */
    public function deleteAction(Request $request, Zone $zone)
    {
        $em = $this->getDoctrine()->getManager();
        $em->remove($zone);
        $em->flush();
        return $this->redirectToRoute('zone_index');
    }


    /**
     * Creates a form to delete a zone entity.
     *
     * @param Zone $zone The zone entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm(Zone $zone)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('zone_delete', array('id' => $zone->getId())))
            ->setMethod('DELETE')
            ->getForm()
        ;
    }


    /**
     * Lists all zones and pays entities.
     *
     * @Route("/zones/pays", name="zones_pays")
     * @Method({"GET", "POST"})
     */
    public function listZonesPaysAction(Request $request)
    {
        $bareme = $request->get('bareme');
        if($bareme!='abonnes' && $bareme!='occasionnels'){
            return $this->redirectToRoute('zone_index');
        }

        //Récupération des zones de facturation et les pays associés
        $em = $this->getDoctrine()->getManager();
        $zone1 = $em->getRepository('AppBundle:Zone')->findOneByName('Zone 1');
        $zone2 = $em->getRepository('AppBundle:Zone')->findOneByName('Zone 2');
        $zone3 = $em->getRepository('AppBundle:Zone')->findOneByName('Zone 3');
        $zone4 = $em->getRepository('AppBundle:Zone')->findOneByName('Zone 4');
        $zone5 = $em->getRepository('AppBundle:Zone')->findOneByName('Zone 5');

        $payszone1='';
        if ($zone1) {
            $payszone1=$this->paysZone($zone1[0]->getId());
        }

        $payszone2='';
        if ($zone2) {
            $payszone2=$this->paysZone($zone2[0]->getId());
        }

        $payszone3='';
        if ($zone3) {
            $payszone3=$this->paysZone($zone3[0]->getId());
        }

        $payszone4='';
        if ($zone4) {
            $payszone4=$this->paysZone($zone4[0]->getId());
        }

        $payszone5='';
        if ($zone5) {
            $payszone5=$this->paysZone($zone5[0]->getId());
        }



        return $this->render('zone/ZonesPays.html.twig', array(
            'payszone1' => $payszone1,
            'payszone2' => $payszone2,
            'payszone3' => $payszone3,
            'payszone4' => $payszone4,
            'payszone5' => $payszone5,
            'bareme' => $bareme,
        ));
    }



    /**
     *
     * @param $zone
     *
     * @return array
     */
    private function paysZone($zone)
    {
        $em = $this->getDoctrine()->getManager();
        return $em->getRepository('AppBundle:Pays')->findByZone($zone);

    }




}
