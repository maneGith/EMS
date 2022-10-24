<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Autorites;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;

/**
 * Autorite controller.
 *
 * @Route("autorites")
 */
class AutoritesController extends Controller
{

    /**
     * Creates a new autorite entity.
     *
     * @Route("/new", name="autorites_new")
     * @Method({"GET", "POST"})
     */
    public function newAction(Request $request)
    {

        $autorite = new Autorites();
        $autorite->setEtat('Désactivé');
        $form = $this->createForm('AppBundle\Form\AutoritesType', $autorite);
        $form->handleRequest($request);


        $em = $this->getDoctrine()->getManager();
        $autorites = $em->getRepository('AppBundle:Autorites')->findAll();


        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($autorite);
            $em->flush();

            return $this->redirectToRoute('autorites_new');
        }

        return $this->render('autorites/new.html.twig', array(
            'autorite' => $autorite,
            'form' => $form->createView(),
            'autorites' => $autorites,
        ));
    }



    /**
     * Displays a form to edit an existing autorite entity.
     *
     * @Route("/{id}/edit", name="autorites_edit")
     * @Method({"GET", "POST"})
     */
    public function editAction(Request $request, Autorites $autorite)
    {
        $deleteForm = $this->createDeleteForm($autorite);
        $editForm = $this->createForm('AppBundle\Form\AutoritesType', $autorite);
        $editForm->handleRequest($request);

        if ($editForm->isSubmitted() && $editForm->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('autorites_edit', array('id' => $autorite->getId()));
        }

        return $this->render('autorites/edit.html.twig', array(
            'autorite' => $autorite,
            'edit_form' => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * @Route("/activation", name="autorite_acti")
     */
    public function activerautoriteAction(Request $request)
    {
        $id=$request->get('id');
        $titre1 = $request->get('titre');
        $titre2='';

        $em = $this->getDoctrine()->getManager();
        $autorites = $em->getRepository('AppBundle:Autorites')->findAll();

        if($titre1=='DGTITULAIRE'){
            $titre2='DGINTERIM';
        }elseif($titre1=='DGINTERIM'){
            $titre2='DGTITULAIRE';
        }elseif($titre1=='DAFCTITULAIRE'){
            $titre2='DAFCINTERIM';
        }else{
            $titre2='DAFCTITULAIRE';
        }

        // Désactivation des autorités
        $autorites = $em->getRepository('AppBundle:Autorites')->findByTitres($titre1, $titre2);

        if( count($autorites) > 0)
        {
            foreach($autorites as $key => $value)
            {

                    $autorites[$key]->setEtat('Désactivé');
                    $em->persist($autorites[$key]);
                    $em->flush();
            }
        }


       // var_dump($autorites);die();


        //Activation de l'autorité
        $autorite = $em->getRepository('AppBundle:Autorites')->findOneById($id)[0];
        $autorite->setEtat('Activé');
        $em->persist($autorite);
        $em->flush();

        return $this->redirectToRoute('autorites_new');
    }

    /**
     * Deletes a autorite entity.
     *
     * @Route("/{id}", name="autorites_delete")
     * @Method("DELETE")
     */
    public function deleteAction(Request $request, Autorites $autorite)
    {
        $form = $this->createDeleteForm($autorite);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->remove($autorite);
            $em->flush();
        }

        return $this->redirectToRoute('autorites_new');
    }

    /**
     * Creates a form to delete a autorite entity.
     *
     * @param Autorites $autorite The autorite entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm(Autorites $autorite)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('autorites_delete', array('id' => $autorite->getId())))
            ->setMethod('DELETE')
            ->getForm()
        ;
    }
}
