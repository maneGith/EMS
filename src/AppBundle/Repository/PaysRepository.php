<?php

namespace AppBundle\Repository;

/**
 * PaysRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class PaysRepository extends \Doctrine\ORM\EntityRepository
{
    public function findByZone($zone)
    {
        return $this->getEntityManager()
            ->createQuery(
                'SELECT p FROM AppBundle:Pays p
                 WHERE p.zone = :zones
                 ORDER BY p.name '
            )->setParameter('zones', $zone)
            ->getResult();
    }

    public function findByName()
    {
        return $this->getEntityManager()
            ->createQuery(
                'SELECT p FROM AppBundle:Pays p
                 ORDER BY p.name'
            )->getResult();
    }

    public function findOneById($id)
    {
        return $this->getEntityManager()
            ->createQuery(
                'SELECT p FROM AppBundle:Pays p
                 WHERE p.id = :id'
            )->setParameter('id', $id)
            ->getResult();
    }

    public function findOneByName($name)
    {
        return $this->getEntityManager()
            ->createQuery(
                'SELECT p FROM AppBundle:Pays p
                 WHERE p.name = :name'
            )->setParameter('name', $name)
            ->getResult();
    }
}
