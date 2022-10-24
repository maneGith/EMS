<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * AbonneEnvoi
 *
 * @ORM\Table(name="abonne_envoi")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\AbonneEnvoiRepository")
 */
class AbonneEnvoi
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
     * @ORM\ManyToOne(targetEntity="Abonne")
     *
     * @ORM\JoinColumn(name="abonne", referencedColumnName="id")
     */
    private $abonne;

    /**
     * @ORM\ManyToOne(targetEntity="Envoi", cascade={"persist","remove"})
     *
     * @ORM\JoinColumn(name="envoi", referencedColumnName="id")
     */
    private $envoi;


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
     * @return mixed
     */
    public function getEnvoi()
    {
        return $this->envoi;
    }

    /**
     * @param mixed $envoi
     */
    public function setEnvoi($envoi)
    {
        $this->envoi = $envoi;
    }
}

