<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Depeche
 *
 * @ORM\Table(name="depeche")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\DepecheRepository")
 */
class Depeche
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="numero", type="string", length=255)
     *
     */
    private $numero;


    /**
     * @var string
     *
     * @ORM\Column(name="type", type="string", length=255)
     *
     */
    private $type;

    /**
     * @var string
     *
     * @ORM\Column(name="journee", type="string", length=255)
     */
    private $journee;


    /**
     * @ORM\ManyToOne(targetEntity="Agence")
     *
     * @ORM\JoinColumn(name="agence", referencedColumnName="id", nullable=true)
     */
    private $agence;

    /**
     * @ORM\ManyToOne(targetEntity="Agence")
     *
     * @ORM\JoinColumn(name="destination", referencedColumnName="id", nullable=true)
     */
    private $destination;

    /**
     * @var string
     *
     * @ORM\Column(name="etat", type="string", length=255)
     */
    private $etat;
    
    /**
     * @var string
     *
     * @ORM\Column(name="heure", type="string", length=255)
     */
    private $heure;


    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getNumero()
    {
        return $this->numero;
    }

    /**
     * @param string $numero
     */
    public function setNumero($numero)
    {
        $this->numero = $numero;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param string $type
     */
    public function setType($type)
    {
        $this->type = $type;
    }

    /**
     * @return string
     */
    public function getJournee()
    {
        return $this->journee;
    }

    /**
     * @param string $journee
     */
    public function setJournee($journee)
    {
        $this->journee = $journee;
    }

    /**
     * @return mixed
     */
    public function getDestination()
    {
        return $this->destination;
    }

    /**
     * @param mixed $destination
     */
    public function setDestination($destination)
    {
        $this->destination = $destination;
    }

    /**
     * @return mixed
     */
    public function getAgence()
    {
        return $this->agence;
    }

    /**
     * @param mixed $agence
     */
    public function setAgence($agence)
    {
        $this->agence = $agence;
    }

    /**
     * @return string
     */
    public function getEtat()
    {
        return $this->etat;
    }

    /**
     * @param string $etat
     */
    public function setEtat($etat)
    {
        $this->etat = $etat;
    }
    
     /**
     * @return string
     */
    public function getHeure()
    {
        return $this->heure;
    }

    /**
     * @param string $heure
     */
    public function setHeure($heure)
    {
        $this->heure = $heure;
    }
}

