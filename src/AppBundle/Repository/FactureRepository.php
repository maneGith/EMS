<?php

namespace AppBundle\Repository;

/**
 * FactureRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class FactureRepository extends \Doctrine\ORM\EntityRepository
{
    public function findOneByIdAbonnePeriodeEtat($id_abonne, $periode, $recouvree)
    {
        return $this->getEntityManager()
            ->createQuery(
                'SELECT f FROM AppBundle:Facture f
                 WHERE f.abonne = :abonne
                   AND f.abonne = :abonne
                   AND f.periode = :periode
                  AND f.etat = :etat'
            )->setParameter('abonne', $id_abonne)
            ->setParameter('periode', $periode)
            ->setParameter('etat', $recouvree)
            ->getResult();
    }

    public function findOneByIdAbonnePeriode($id_abonne, $periode)
    {
        return $this->getEntityManager()
            ->createQuery(
                'SELECT f FROM AppBundle:Facture f
                 WHERE f.abonne = :abonne
                   AND f.periode = :periode'
            )->setParameter('abonne', $id_abonne)
            ->setParameter('periode', $periode)
            ->getResult();
    }

    public function findByPeriodeFacturesAbonnes($periode)
    {
        return $this->getEntityManager()
            ->createQuery(
                'SELECT f FROM AppBundle:Facture f
                  JOIN f.abonne a
                 WHERE f.periode = :periode
                 ORDER BY  a.nom'
            )
            ->setParameter('periode', $periode)
            ->getResult();
    }

    public function findByPeriodeAbonnes($periode)
    {
        return $this->getEntityManager()
            ->createQuery(
                'SELECT f FROM AppBundle:Facture f
                  JOIN f.abonne a
                 WHERE f.periode = :periode
                 ORDER BY  a.nom'
            )
            ->setParameter('periode', $periode)
            ->getResult();
    }
}
