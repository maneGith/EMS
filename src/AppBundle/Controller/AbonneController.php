<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Abonne;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;

/**
 * Abonne controller.
 *
 * @Route("assistant/abonne")
 */
class AbonneController extends Controller
{
    /**
     * Lists all abonne entities.
     *
     * @Route("/", name="abonne_index")
     * @Method({"GET", "POST"})
     */
    public function indexAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        $abonnes = $em->getRepository('AppBundle:Abonne')->findByAbonnes();
        
        //Creation
        $abonne = new Abonne();
        $form = $this->createForm('AppBundle\Form\AbonneType', $abonne);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            //Affectation provisoire
            $abonne->setCodeabonne('222');

            $em = $this->getDoctrine()->getManager();
            $em->persist($abonne);
            $em->flush();

            return $this->redirectToRoute('abonne_edit', array('id' => $abonne->getId()));
        }
        //Fin 

        return $this->render('abonne/index.html.twig', array(
            'abonnes' => $abonnes,
            'abonne' => $abonne,
            'form' => $form->createView(),
        ));
    }

    /**
     * Creates a new abonne entity.
     *
     * @Route("/new", name="abonne_new")
     * @Method({"GET", "POST"})
     */
    public function newAction(Request $request)
    {
        $abonne = new Abonne();
        $form = $this->createForm('AppBundle\Form\AbonneType', $abonne);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            //Affectation provisoire
            $abonne->setCodeabonne('222');

            $em = $this->getDoctrine()->getManager();
            $em->persist($abonne);
            $em->flush();

            return $this->redirectToRoute('abonne_edit', array('id' => $abonne->getId()));
        }

        return $this->render('abonne/new.html.twig', array(
            'abonne' => $abonne,
            'form' => $form->createView(),
        ));
    }


    /**
     * Displays a form to edit an existing abonne entity.
     *
     * @Route("/{id}/edit", name="abonne_edit")
     * @Method({"GET", "POST"})
     */
    public function editAction(Request $request, Abonne $abonne)
    {

        $editForm = $this->createForm('AppBundle\Form\AbonneType', $abonne);
        $editForm->handleRequest($request);

        if ($editForm->isSubmitted() && $editForm->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('abonne_edit', array('id' => $abonne->getId()));
        }

        return $this->render('abonne/edit.html.twig', array(
            'abonne' => $abonne,
            'edit_form' => $editForm->createView(),
        ));
    }

    /**
     * Deletes a abonne entity.
     *
     * @Route("/{id}", name="abonne_delete")
     */
    public function deleteAction(Abonne $abonne)
    {
            $em = $this->getDoctrine()->getManager();
            $em->remove($abonne);
            $em->flush();
            return $this->redirectToRoute('abonne_index');
    }
}
