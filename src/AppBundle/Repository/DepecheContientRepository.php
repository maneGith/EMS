<?php

namespace AppBundle\Repository;

/**
 * DepecheContientRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class DepecheContientRepository extends \Doctrine\ORM\EntityRepository
{
    public function findOneByIdEnvoi($envoi)
    {
        return $this->getEntityManager()
            ->createQuery(
                'SELECT d FROM AppBundle:DepecheContient d
                     WHERE d.envoi = :envoi'
            )->setParameter('envoi', $envoi)
            ->getResult();
    }
    
    
    public function findByIdDepeche($depeche)
    {
        return $this->getEntityManager()
            ->createQuery(
                'SELECT d FROM AppBundle:DepecheContient d
                 JOIN d.envoi e
                 JOIN e.destinataire b
                 JOIN b.usager u
                 
                 WHERE  d.depeche=:depeche
                 ORDER BY u.ville ASC'
                    )->setParameter('depeche', $depeche)
            ->getResult();
    }
    
    public function findByIdDepecheInt($depeche)
    {
        return $this->getEntityManager()
            ->createQuery(
                'SELECT d FROM AppBundle:DepecheContient d
                 JOIN d.envoi e
                 JOIN e.destinataire b
                 JOIN b.usager u
                 JOIN u.pays p
                 WHERE  d.depeche=:depeche
                 ORDER BY p.name ASC'
                    )->setParameter('depeche', $depeche)
            ->getResult();
    }
}
