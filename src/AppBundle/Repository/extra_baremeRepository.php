<?php

namespace AppBundle\Repository;

/**
 * extra_baremeRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class extra_baremeRepository extends \Doctrine\ORM\EntityRepository
{
    public function findByPoidsClient($client, $poids)
    {
        return $this->getEntityManager()
            ->createQuery(
                'SELECT b FROM AppBundle:extra_bareme b
                 WHERE  b.client = :client
                 AND b.poidsmin < :poids
                 AND b.poidsmax >= :poids'
            )->setParameter('client', $client)
            ->setParameter('poids', $poids)
            ->getResult();
    }
}
