<?php

namespace AppBundle\Repository;

/**
 * UtilisateurRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class UtilisateurRepository extends \Doctrine\ORM\EntityRepository
{
    public function findOneByUsername($username)
    {
        return $this->getEntityManager()
            ->createQuery(
                'SELECT u FROM AppBundle:Utilisateur u
                 WHERE u.username = :username'
            )->setParameter('username', $username)
             ->getResult();
    }

    public function findOneById($id)
    {
        return $this->getEntityManager()
            ->createQuery(
                'SELECT u FROM AppBundle:Utilisateur u
                 WHERE u.id = :id'
            )->setParameter('id', $id)
            ->getResult();
    }

    public function findeByIdAGUsers($agence)
    {
        return $this->getEntityManager()
            ->createQuery(
                'SELECT u FROM AppBundle:Utilisateur u
                 WHERE u.agence = :agence
                 ORDER BY u.username'
            )->setParameter('agence', $agence)
            ->getResult();
    }

    public function findOneByUsernameId($username, $id)
    {
        return $this->getEntityManager()
            ->createQuery(
                'SELECT u FROM AppBundle:Utilisateur u
                 WHERE u.username = :username
                 AND u.id  <> :id'
            )->setParameter('username', $username)
            ->setParameter('id', $id)
            ->getResult();
    }

    public function findByUsers()
    {
        return $this->getEntityManager()
            ->createQuery(
                'SELECT u FROM AppBundle:Utilisateur u
                 WHERE u.profil  <> :profil'
            )->setParameter('profil', 'ADMIN')
            ->getResult();
    }

    public function findByProfil()
    {
        return $this->getEntityManager()
            ->createQuery(
                'SELECT DISTINCT u.profil FROM AppBundle:Utilisateur u
                        WHERE u.profil  <> :profil
                        ORDER BY u.profil'
                         )->setParameter('profil', 'ADMIN')
                             ->getResult();
    }

    public function findByProfilAgenceNom($profil)
    {
        return $this->getEntityManager()
            ->createQuery(
                'SELECT DISTINCT u, a FROM AppBundle:Utilisateur u
                 JOIN u.agence a
                 WHERE u.profil = :profil
                        ORDER BY  a.statut, a.nom, u.nom'
            )->setParameter('profil', $profil)
                ->getResult();
    }


    public function findOneByEmail($email)
    {
        return $this->getEntityManager()
            ->createQuery(
                'SELECT u FROM AppBundle:Utilisateur u
                 WHERE u.email = :email'
            )->setParameter('email', $email)
            ->getResult();
    }

    public function findOneByEmailId($email, $id)
    {
        return $this->getEntityManager()
            ->createQuery(
                'SELECT u FROM AppBundle:Utilisateur u
                 WHERE u.email = :email
                 AND u.id <> :id'
            )->setParameter('email', $email)
            ->setParameter('id', $id)
            ->getResult();
    }

    public function findByEnabled($enabled)
    {
        return $this->getEntityManager()
            ->createQuery(
                'SELECT u FROM AppBundle:Utilisateur u
                 WHERE u.enabled = :enabled'
            )->setParameter('enabled', $enabled)
            ->getResult();
    }

    //Les d'int??grit??s fonctionnelles
        //On ne peut pas avoir deux Directeurs generaux actifs
       //Deux DEX  actifs et Assistants DEX actifs
    public function findOneByProfilEtatActif($profil,$agence)
    {
        return $this->getEntityManager()
            ->createQuery(
                'SELECT u FROM AppBundle:Utilisateur u
                 WHERE u.profil = :profil
                 AND   u.enabled = :enabled
                 AND   u.agence = :agence'
            )->setParameter('profil', $profil)
             ->setParameter('agence', $agence)
             ->setParameter('enabled', '1')
             ->getResult();
    }
    //On ne peut pas avoir deux Chefs d'agence actifs
       //Pour une meme agence
}
