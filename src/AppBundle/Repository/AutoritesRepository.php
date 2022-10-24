<?php

namespace AppBundle\Repository;

/**
 * AutoritesRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class AutoritesRepository extends \Doctrine\ORM\EntityRepository
{
    public function findOneById($autorite)
    {
        return $this->getEntityManager()
            ->createQuery(
                'SELECT a FROM AppBundle:Autorites a
                     WHERE a.id = :id'
            )->setParameter('id', $autorite)
            ->getResult();
    }

    public function findByTitres($titreT, $titreI)
    {
        return $this->getEntityManager()
            ->createQuery(
                'SELECT  a FROM AppBundle:Autorites a
                  WHERE a.titre = :titreT
                   OR   a.titre = :titreI'
            )->setParameter('titreT', $titreT)
             ->setParameter('titreI', $titreI)
             ->getResult();
    }

    public function findByTitreActif($titreT, $titreI)
    {
        return $this->getEntityManager()
            ->createQuery(
                'SELECT  a FROM AppBundle:Autorites a
                  WHERE (a.titre = :titreT
                   OR   a.titre = :titreI)
                   AND a.etat = :etat'
            )->setParameter('titreT', $titreT)
            ->setParameter('titreI', $titreI)
            ->setParameter('etat', 'Activé')
            ->getResult();
    }
}
