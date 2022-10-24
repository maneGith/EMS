<?php

namespace AppBundle\Repository;

/**
 * EnvoiRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class EnvoiRepository extends \Doctrine\ORM\EntityRepository
{
    public function findOneById($envoi)
    {
        return $this->getEntityManager()
            ->createQuery(
                'SELECT e FROM AppBundle:Envoi e
                     WHERE e.id = :id'
            )->setParameter('id', $envoi)
            ->getResult();
    }

    public function findByBordereau($bordereau)
    {
        return $this->getEntityManager()
                ->createQuery(
                    'SELECT e FROM AppBundle:Envoi e
                     WHERE e.bordereau = :bordereau
                     AND e.etat != :suppr'
                )->setParameter('bordereau', $bordereau)
                 ->setParameter('suppr', 'suppr')
                 ->getResult();
    }

    public function findByJourneeMois($journneemois)
    {
        return $this->getEntityManager()
            ->createQuery(
                'SELECT DISTINCT e.date FROM AppBundle:Envoi e
                      WHERE e.date LIKE :journneemois
                      ORDER BY e.date'
            )->setParameter('journneemois', $journneemois)
            ->getResult();
    }

    public function findByJourneeBR($journnee, $id_agence)
    {
        return $this->getEntityManager()
            ->createQuery(
                'SELECT  DISTINCT e.date,  b.id, b.typeenvoi, b.numbdr, a.nom FROM AppBundle:Envoi e
                  JOIN e.bordereau b
                  JOIN e.agence a
                  WHERE e.date LIKE :journnee
                  AND  a.id = :id
                  ORDER BY  b.typeenvoi DESC , e.date'
            )->setParameter('journnee', $journnee)
             ->setParameter('id', $id_agence)
             ->getResult();
    }

    public function findOneByCodeenvoi($codeenvoi)
    {
        return $this->getEntityManager()
            ->createQuery(
                'SELECT e FROM AppBundle:Envoi e
                     WHERE e.codeenvoi = :codeenvoi
                     AND e.etat != :suppr'
            )->setParameter('codeenvoi', $codeenvoi)
            ->setParameter('suppr', 'suppr')
            ->getResult();
    }

    public function findOneByCode($code)
    {
        return $this->getEntityManager()
            ->createQuery(
                'SELECT e FROM AppBundle:Envoi e
                 WHERE UPPER(e.codeenvoi) = :codeenvoi'
            )->setParameter('codeenvoi', $code)
            ->getResult();
    }

    public function findTotalByMoisAgent($utilisateur, $typeenvoi,  $mois, $journee)
    {
        return $this->getEntityManager()
            ->createQuery(
                'SELECT e, b FROM AppBundle:Envoi e
                 JOIN e.bordereau b
                 JOIN b.vacation v
                 WHERE  v.utilisateur = :utilisateur
                 AND   b.typeenvoi = :typeenvoi
                 AND   e.date LIKE :mois
                 AND e.date < :journee
                  AND e.etat != :suppr'
            )->setParameter('utilisateur', $utilisateur)
            ->setParameter('typeenvoi', $typeenvoi)
             ->setParameter('mois', $mois)
            ->setParameter('journee', $journee)
            ->setParameter('suppr', 'suppr')
            ->getResult();
    }

    public function findByMois($mois)
    {
        return $this->getEntityManager()
            ->createQuery(
                'SELECT e, a FROM AppBundle:Envoi e
                 JOIN e.agence a
                 WHERE e.date LIKE :mois
                 ORDER BY e.agence'
                )->setParameter('mois', $mois)
                ->getResult();
    }

    public function findByMoisAgence($id, $mois)
    {
        return $this->getEntityManager()
            ->createQuery(
                'SELECT e FROM AppBundle:Envoi e
                 WHERE e.agence=:agence
                 AND  e.date LIKE :mois'
            )->setParameter('agence', $id)
            ->setParameter('mois', $mois)
            ->getResult();
    }

    public function findByMoisAgenceEchelleClient($id, $mois, $client, $echelle)
    {
        return $this->getEntityManager()
            ->createQuery(
                'SELECT MAX(SUBSTRING(e.codeenvoi,4,4)) as code FROM AppBundle:Envoi e
                 WHERE e.agence=:agence
                 AND e.client=:client
                 AND e.echelle=:echelle
                 AND  e.date LIKE :mois
                 AND e.etat != :suppr'
            )->setParameter('agence', $id)
            ->setParameter('client', $client)
            ->setParameter('echelle', $echelle)
            ->setParameter('mois', $mois)
            ->setParameter('suppr', 'suppr')
            ->getResult();
    }
    
     public function findByNEnvois($id, $mois, $client, $echelle)
    {
        return $this->getEntityManager()
            ->createQuery(
                'SELECT  e.codeenvoi FROM AppBundle:Envoi e
                 WHERE e.agence=:agence
                 AND e.client=:client
                 AND e.echelle=:echelle
                 AND  e.date LIKE :mois
                 AND e.etat != :suppr'
            )->setParameter('agence', $id)
            ->setParameter('client', $client)
            ->setParameter('echelle', $echelle)
            ->setParameter('mois', $mois)
            ->setParameter('suppr', 'suppr')
            ->getResult();
    }

    public function findByAgence($agence, $client, $date, $echelle)
    {
        return $this->getEntityManager()
            ->createQuery(
                'SELECT e FROM AppBundle:Envoi e
                     WHERE e.agence = :agence
                     AND   e.client = :client
                     AND   e.date LIKE :mois
                     AND   e.echelle = :echelle
                     AND e.etat != :suppr'
            )->setParameter('agence', $agence)
            ->setParameter('client', $client)
            ->setParameter('mois', $date)
            ->setParameter('echelle', $echelle)
            ->setParameter('suppr', 'suppr')
            ->getResult();



    }

    public function findByEchelonun($client, $date, $echelle)
    {
        return $this->getEntityManager()
            ->createQuery(
                'SELECT e FROM AppBundle:Envoi e
                     WHERE e.poids <= 0.5
                     AND   e.client = :client
                     AND   e.date LIKE :mois
                     AND   e.echelle = :echelle
                      AND e.etat != :suppr'
            )->setParameter('client', $client)
            ->setParameter('mois', $date)
            ->setParameter('echelle', $echelle)
            ->setParameter('suppr', 'suppr')
            ->getResult();
    }

    public function findByEchelondeux($client, $date, $echelle)
{
    return $this->getEntityManager()
        ->createQuery(
            'SELECT e FROM AppBundle:Envoi e
                     WHERE e.poids > 0.5
                     AND   e.poids <= 2.0
                     AND   e.client = :client
                     AND   e.date LIKE :mois
                     AND   e.echelle = :echelle
                      AND e.etat != :suppr'
        )->setParameter('client', $client)
        ->setParameter('mois', $date)
        ->setParameter('echelle', $echelle)
        ->setParameter('suppr', 'suppr')
        ->getResult();
}

    public function findByEchelontrois($client, $date, $echelle)
    {
        return $this->getEntityManager()
            ->createQuery(
                'SELECT e FROM AppBundle:Envoi e
                     WHERE e.poids > 2.0
                     AND   e.poids <= 20.0
                     AND   e.client = :client
                     AND   e.date LIKE :mois
                     AND   e.echelle = :echelle
                      AND e.etat != :suppr'
            )->setParameter('client', $client)
            ->setParameter('mois', $date)
            ->setParameter('echelle', $echelle)
            ->setParameter('suppr', 'suppr')
            ->getResult();
    }


    public function findByEchelonquatre($client, $date, $echelle)
    {
        return $this->getEntityManager()
            ->createQuery(
                'SELECT e FROM AppBundle:Envoi e
                     WHERE e.poids > 20.0
                     AND   e.client = :client
                     AND   e.date LIKE :mois
                     AND   e.echelle = :echelle
                      AND e.etat != :suppr'
            )->setParameter('client', $client)
            ->setParameter('mois', $date)
            ->setParameter('echelle', $echelle)
            ->setParameter('suppr', 'suppr')
            ->getResult();
    }

    public function findByIdBLMaxEnvois($id_bord)
    {
        return $this->getEntityManager()
            ->createQuery(
                'SELECT MAX(SUBSTRING(e.codeenvoi,4,4)) as code FROM AppBundle:Envoi e
                     WHERE e.bordereau = :bordereau'
            )->setParameter('bordereau', $id_bord)
            ->getResult();
    }
    
    
    
    public function findMaxByAnneeDepotAndEchelle($anneecivil, $echelle)
    {
        return $this->getEntityManager()
            ->createQuery(
                'SELECT e FROM AppBundle:Envoi e
                 WHERE e.date LIKE :anneecivil
                 AND   e.echelle=:echelle'
            )->setParameter('anneecivil', $anneecivil)
            ->setParameter('echelle', $echelle)
            ->getResult();
    }
    
    
    public function findByMoisAgenceDepeche($agence, $periode , $periode2, $echelle)
    {
        return $this->getEntityManager()
            ->createQuery(
                'SELECT e FROM AppBundle:Envoi e
                 WHERE  (e.date LIKE :date
                 OR  e.date LIKE :datep)
                 AND  e.agence=:agence
                 AND e.echelle=:echelle
                 '
            )->setParameter('agence', $agence)
            ->setParameter('date', $periode)
                ->setParameter('datep', $periode2)
                 ->setParameter('echelle', $echelle)
            ->getResult();
    }
}