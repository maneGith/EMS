<?php

namespace AppBundle\Repository;

/**
 * ZoneRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class ZoneRepository extends \Doctrine\ORM\EntityRepository
{
    public function findOneByName($nom)
    {
        return $this->getEntityManager()
            ->createQuery(
                'SELECT z FROM AppBundle:Zone z
                 WHERE z.name = :nom'
            )->setParameter('nom', $nom)
            ->getResult();
    }

    public function findOneById($id)
    {
        return $this->getEntityManager()
            ->createQuery(
                'SELECT z FROM AppBundle:Zone z
                 WHERE z.id = :id'
            )->setParameter('id', $id)
            ->getResult();
    }
}
