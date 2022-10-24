<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Facture;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\Request;


/**
 * Facture controller.
 *
 * @Route("/facture")
 */
class FactureController extends Controller
{

    /**
     * Displays a form to edit an existing facture entity.
     *
     * @Route("/create/edit", name="facture_createedit")
     * @Method({"GET", "POST"})
     */
    public function editAction(Request $request)
    {
        $id_abonne = $request->get('abonne');
        $periode = $request->get('periode');
        //var_dump($abonne);die();
        $etat = $request->get('etat');
        $em = $this->getDoctrine()->getManager();
        $facture=$em->getRepository('AppBundle:Facture')->findOneByIdAbonnePeriode($id_abonne, $periode);


            if($facture){
                $facture[0]->setEtat($etat);
                $em->persist( $facture[0]);
                $em->flush();
            }


        return $this->redirectToRoute('recouvrement',array('periode'=>$periode));
    }


    /**
     * Displays a form to edit an existing facture entity.
     *
     * @Route("/{id}/edit", name="facture_edit")
     * @Method({"GET", "POST"})
     */
    public function editsAction(Request $request, Facture $facture)
    {
        $deleteForm = $this->createDeleteForm($facture);
        $editForm = $this->createForm('AppBundle\Form\FactureType', $facture);
        $editForm->handleRequest($request);

        if ($editForm->isSubmitted() && $editForm->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('facture_edit', array('id' => $facture->getId()));
        }

        return $this->render('facture/edit.html.twig', array(
            'facture' => $facture,
            'edit_form' => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ));
    }

}
