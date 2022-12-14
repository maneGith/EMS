<?php

namespace AppBundle\Repository;

/**
 * BaremeRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class BaremeRepository extends \Doctrine\ORM\EntityRepository
{
    public function findByDomaine1($domaine, $libtaxe)
    {
        return $this->getEntityManager()
            ->createQuery(
                'SELECT b FROM AppBundle:Bareme b
                 WHERE b.domaine = :domaine
                 AND b.poidsmax <= 15.0
                 AND b.typeclient = :typeclient
                 ORDER BY b.poidsmin ASC'
            )->setParameter('domaine', $domaine)
            ->setParameter('typeclient', $libtaxe)
            ->getResult();
    }

    public function findByDomaine2($domaine, $libtaxe)
    {
        return $this->getEntityManager()
            ->createQuery(
                'SELECT b FROM AppBundle:Bareme b
                 WHERE b.domaine = :domaine
                 AND b.poidsmax > 15.0
                 AND b.typeclient = :typeclient
                 ORDER BY b.poidsmin ASC'
            )->setParameter('domaine', $domaine)
            ->setParameter('typeclient', $libtaxe)
            ->getResult();
    }


    public function findByDomaineTypedocClient1($domaine, $client, $doc)
    {
        return $this->getEntityManager()
            ->createQuery(
                'SELECT b FROM AppBundle:Bareme b
                 WHERE b.domaine = :domaine
                 AND b.client = :client
                 AND b.document = :document
                 AND b.poidsmax <= 15.0
                 ORDER BY b.poidsmin ASC'
            )->setParameter('domaine', $domaine)
             ->setParameter('client', $client)
             ->setParameter('document', $doc)
             ->getResult();
    }

    public function findByDomaineTypedocClient2($domaine, $client, $doc)
    {
        return $this->getEntityManager()
            ->createQuery(
                'SELECT b FROM AppBundle:Bareme b
                 WHERE b.domaine = :domaine
                  AND b.client = :client
                 AND b.document = :document
                 AND b.poidsmax > 15.0
                 ORDER BY b.poidsmin ASC'
            )->setParameter('domaine', $domaine)
            ->setParameter('client', $client)
            ->setParameter('document', $doc)
            ->getResult();
    }

    public function findByDocClientDomaine($doc, $client, $domaine, $libtaxe)
    {
        return $this->getEntityManager()
            ->createQuery(
                'SELECT b FROM AppBundle:Bareme b
                 WHERE b.domaine = :domaine
                 AND b.client = :client
                 AND b.document = :document
                 AND b.typeclient = :typeclient'
            )->setParameter('document', $doc)
             ->setParameter('client', $client)
             ->setParameter('domaine', $domaine)
            ->setParameter('typeclient', $libtaxe)
             ->getResult();
    }

    public function findByPoidsDocClientDomaine($document, $client, $domaine, $poids, $libtaxe)
    {
        return $this->getEntityManager()
            ->createQuery(
                'SELECT b FROM AppBundle:Bareme b
                 WHERE b.domaine = :domaine
                 AND b.client = :client
                 AND b.document = :document
                 AND b.poidsmin < :poids
                 AND b.poidsmax >= :poids
                 AND b.typeclient = :typeclient'
            )->setParameter('document', $document)
             ->setParameter('client', $client)
             ->setParameter('domaine', $domaine)
             ->setParameter('poids', $poids)
            ->setParameter('typeclient', $libtaxe)
             ->getResult();
    }
}
