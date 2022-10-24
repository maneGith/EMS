<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Expediteur
 *
 * @ORM\Table(name="expediteur")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\ExpediteurRepository")
 */
class Expediteur
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
     * @ORM\Column(name="numcompte", type="string", length=255)
     */
    private $numcompte;

    /**
     * @var string
     *
     * @ORM\Column(name="civilite", type="string", length=255)
     */
    private $civilite;

    /**
     * @ORM\ManyToOne(targetEntity="Usager", cascade={"persist","remove"})
     *
     * @ORM\JoinColumn(name="usager", referencedColumnName="id")
     */
    private $usager;


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
     * @return mixed
     */
    public function getUsager()
    {
        return $this->usager;
    }

    /**
     * @param mixed $usager
     */
    public function setUsager($usager)
    {
        $this->usager = $usager;
    }

    /**
     * @return mixed
     */
    public function getNumcompte()
    {
        return $this->numcompte;
    }

    /**
     * @param mixed $numcompte
     */
    public function setNumcompte($numcompte)
    {
        $this->numcompte = $numcompte;
    }

    /**
     * @return mixed
     */
    public function getCivilite()
    {
        return $this->civilite;
    }

    /**
     * @param mixed $civilite
     */
    public function setCivilite($civilite)
    {
        $this->civilite = $civilite;
    }

}

