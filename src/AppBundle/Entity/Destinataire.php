<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Destinataire
 *
 * @ORM\Table(name="destinataire")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\DestinataireRepository")
 */
class Destinataire
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

}

