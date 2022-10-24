<?php

namespace AppBundle\Repository;

/**
 * BordereauRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class BordereauRepository extends \Doctrine\ORM\EntityRepository
{

    public function findOneById($id)
    {
        return $this->getEntityManager()
            ->createQuery(
                'SELECT b, v FROM AppBundle:Bordereau b
                 JOIN b.vacation v
                 WHERE b.id = :id'
            )->setParameter('id', $id)
            ->getResult();
    }

    public function findBordTpypeEnvVacationAgentJour($typeenvoi, $vacation)
    {
        return $this->getEntityManager()
            ->createQuery(
                'SELECT b, v FROM AppBundle:Bordereau b
                 JOIN b.vacation v
                 WHERE b.typeenvoi = :typeenvoi
                 AND   b.vacation = :vacation'
            )->setParameter('typeenvoi', $typeenvoi)
            ->setParameter('vacation', $vacation)
            ->getResult();
    }

    public function findByMoisAgence($mois, $agence)
    {
        return $this->getEntityManager()
            ->createQuery(
                'SELECT b, v FROM AppBundle:Bordereau b
                 JOIN b.vacation v
                 WHERE v.journee LIKE :mois
                  AND v.agence = :agence'
            )->setParameter('mois', $mois)
            ->setParameter('agence', $agence)
            ->getResult();
    }

    public function findByMoisAgenceType($mois, $agence, $typeenvoi)
    {
        return $this->getEntityManager()
            ->createQuery(
                'SELECT b, v FROM AppBundle:Bordereau b
                 JOIN b.vacation v
                 WHERE v.journee LIKE :mois
                  AND v.agence = :agence
                  AND  b.typeenvoi = :typeenvoi'
            )->setParameter('mois', $mois)
            ->setParameter('agence', $agence)
            ->setParameter('typeenvoi', $typeenvoi)
            ->getResult();
    }

    public function findByIdVacation($id_vacation)
    {
        return $this->getEntityManager()
            ->createQuery(
                'SELECT b FROM AppBundle:Bordereau b
                 WHERE  b.vacation = :vacation'
            )->setParameter('vacation', $id_vacation)
            ->getResult();
    }
}
