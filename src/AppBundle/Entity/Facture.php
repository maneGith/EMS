<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Facture
 *
 * @ORM\Table(name="facture")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\FactureRepository")
 */
class Facture
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
     * @ORM\Column(name="periode", type="string", length=255)
     */
    private $periode;

    /**
     * @var string
     *
     * @ORM\Column(name="etat", type="string", length=255)
     */
    private $etat;

    /**
     * @var string
     *
     * @ORM\Column(name="numfacture", type="string", length=255)
     */
    private $numfacture;

    /**
     * @var string
     *
     * @ORM\Column(name="dateedition", type="string", length=255, nullable=true)
     */
    private $dateedition;

    /**
     * @var string
     *
     * @ORM\Column(name="arret", type="string", length=255, nullable=true)
     */
    private $arret;

    /**
     * @var string
     *
     * @ORM\Column(name="virement", type="string", length=255, nullable=true)
     */
    private $virement;

    /**
     * @ORM\ManyToOne(targetEntity="Abonne")
     *
     * @ORM\JoinColumn(name="abonne", referencedColumnName="id")
     */
    private $abonne;



    /**
     * Get id
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set periode
     *
     * @param string $periode
     *
     * @return Facture
     */
    public function setPeriode($periode)
    {
        $this->periode = $periode;

        return $this;
    }

    /**
     * Get periode
     *
     * @return string
     */
    public function getPeriode()
    {
        return $this->periode;
    }


    /**
     * @return mixed
     */
    public function getAbonne()
    {
        return $this->abonne;
    }

    /**
     * @param mixed $abonne
     */
    public function setAbonne($abonne)
    {
        $this->abonne = $abonne;
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
     * @return mixed
     */
    public function getDateedition()
    {
        return $this->dateedition;
    }

    /**
     * @param mixed $dateedition
     */
    public function setDateedition($dateedition)
    {
        $this->dateedition = $dateedition;
    }

    /**
     * @return string
     */
    public function getNumfacture()
    {
        return $this->numfacture;
    }

    /**
     * @param string $numfacture
     */
    public function setNumfacture($numfacture)
    {
        $this->numfacture = $numfacture;
    }

    /**
     * @return mixed
     */
    public function getArret()
    {
        return $this->arret;
    }

    /**
     * @param mixed $arret
     */
    public function setArret($arret)
    {
        $this->arret = $arret;
    }

    /**
     * @return mixed
     */
    public function getVirement()
    {
        return $this->virement;
    }

    /**
     * @param mixed $virement
     */
    public function setVirement($virement)
    {
        $this->virement = $virement;
    }
}

