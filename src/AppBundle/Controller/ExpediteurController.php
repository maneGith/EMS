<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Expediteur;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;use Symfony\Component\HttpFoundation\Request;

/**
 * Expediteur controller.
 *
 * @Route("assistantDEX/client")
 */
class ExpediteurController extends Controller
{
    /**
     * Lists all expediteur entities.
     *
     * @Route("/abonnes", name="expediteur_index")
     * @Method("GET")
     */
    public function indexAbonnesAction()
    {
        $em = $this->getDoctrine()->getManager();

        $abonnes = $em->getRepository('AppBundle:Expediteur')->findAll();

        return $this->render('client/index.html.twig', array(
            'abonnes' => $abonnes,
        ));
    }

    /**
     * Creates a new expediteur entity.
     *
     * @Route("/new", name="expediteur_new")
     * @Method({"GET", "POST"})
     */
    public function newAction(Request $request)
    {
        $expediteur = new Expediteur();
        $expediteur->setNature('Abonné');
        $expediteur->setDate("222");
        $form = $this->createForm('AppBundle\Form\ExpediteurType', $expediteur);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($expediteur);
            $em->flush();

            return $this->redirectToRoute('expediteur_edit', array('id' => $expediteur->getId()));
        }

        return $this->render('client/new.html.twig', array(
            'expediteur' => $expediteur,
            'form' => $form->createView(),
        ));
    }



    /**
     * Displays a form to edit an existing expediteur entity.
     *
     * @Route("/{id}/edit", name="expediteur_edit")
     * @Method({"GET", "POST"})
     */
    public function editAction(Request $request, Expediteur $expediteur)
    {

        $editForm = $this->createForm('AppBundle\Form\ExpediteurType', $expediteur);
        $editForm->handleRequest($request);

        if ($editForm->isSubmitted() && $editForm->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('expediteur_edit', array('id' => $expediteur->getId()));
        }

        return $this->render('client/edit.html.twig', array(
            'expediteur' => $expediteur,
            'edit_form' => $editForm->createView(),

        ));
    }



    /**
     * Deletes a expediteur entity.
     *
     * @Route("/{id}", name="expediteur_delete")
     */
    public function deleteAction( Expediteur $expediteur)
    {
        
        $em = $this->getDoctrine()->getManager();
        $em->remove($expediteur);
        $em->flush();
        return $this->redirectToRoute('expediteur_index');

    }


}
