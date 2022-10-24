<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Pays;
use AppBundle\Entity\Zone;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;use Symfony\Component\HttpFoundation\Request;

/**
 * Pay controller.
 *
 * @Route("pays")
 */
class PaysController extends Controller
{

    /**
     * Creates a new pay entity.
     *
     * @Route("/new", name="pays_new")
     * @Method({"GET", "POST"})
     */
    public function newAction(Request $request)
    {
        $pay = new Pays();
        $id_zone = $request->get('id');
        $name = $request->get('name');

       // var_dump($name);die;
        $em = $this->getDoctrine()->getManager();
        $zone = $em->getRepository('AppBundle:Zone')->findOneById($id_zone);

        $pay->setZone($zone[0]);
        $pay->setName($name);
       // var_dump($pay);die;

        $em->persist($pay);
        $em->flush();

        return $this->redirectToRoute('zone_edit', array('id' => $id_zone));


    }



    /**
     * Displays a form to edit an existing pay entity.
     *
     * @Route("/{id}/edit", name="pays_edit")
     * @Method({"GET", "POST"})
     */
    public function editAction(Request $request, Pays $pay)
    {
        $name = $request->get('name');
        $pay->setName($name);
        $id_zone = $pay->getZone()->getId();

        $em = $this->getDoctrine()->getManager();
        $zone = $em->getRepository('AppBundle:Zone')->findOneById($id_zone);
        $pay->setZone($zone[0]);


        $em->flush();

        return $this->redirectToRoute('zone_edit', array('id' => $id_zone));

    }

    /**
     * Deletes a pay entity.
     *
     * @Route("/{id}", name="pays_delete")
     *
     */
    public function deleteAction(Pays $pay)
    {
            $id_zone = $pay->getZone()->getId();

            $em = $this->getDoctrine()->getManager();
            $em->remove($pay);
            $em->flush();

            return $this->redirectToRoute('zone_edit', array('id' => $id_zone));
    }
}
