<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * DepecheContient
 *
 * @ORM\Table(name="depeche_contient")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\DepecheContientRepository")
 */
class DepecheContient
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
     * @ORM\ManyToOne(targetEntity="Depeche")
     *
     * @ORM\JoinColumn(name="depeche", referencedColumnName="id", nullable=true)
     */
    private $depeche;
    
    /**
     * @ORM\OneToOne(targetEntity="Envoi")
     *
     * @ORM\JoinColumn(name="envoi", referencedColumnName="id", nullable=true)
     */
    private $envoi;


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
     * @return mixed
     */
    public function getDepeche()
    {
        return $this->depeche;
    }

    /**
     * @param mixed $depeche
     */
    public function setDepeche($depeche)
    {
        $this->depeche = $depeche;
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

