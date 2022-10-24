<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Bordereau
 *
 * @ORM\Table(name="bordereau")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\BordereauRepository")
 */
class Bordereau
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
     * @ORM\Column(name="typeenvoi", type="string", length=255)
     */
    private $typeenvoi;

    /**
     * @ORM\ManyToOne(targetEntity="Vacation")
     *
     * @ORM\JoinColumn(name="vacation", referencedColumnName="id")
     */
    private $vacation;

    /**
     * @var string
     *
     * @ORM\Column(name="numbdr", type="string", length=255)
     */
    private $numbdr;

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
    public function getVacation()
    {
        return $this->vacation;
    }

    /**
     * @param mixed $vacation
     */
    public function setVacation($vacation)
    {
        $this->vacation = $vacation;
    }

    /**
     * @return string
     */
    public function getTypeenvoi()
    {
        return $this->typeenvoi;
    }

    /**
     * @param string $typeenvoi
     */
    public function setTypeenvoi($typeenvoi)
    {
        $this->typeenvoi = $typeenvoi;
    }

    /**
     * @return string
     */
    public function getNumbdr()
    {
        return $this->numbdr;
    }

    /**
     * @param string $numbdr
     */
    public function setNumbdr($numbdr)
    {
        $this->numbdr = $numbdr;
    }
}

