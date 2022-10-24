<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * extra_bareme
 *
 * @ORM\Table(name="extra_bareme")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\extra_baremeRepository")
 */
class extra_bareme
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
     * @ORM\Column(name="poidsmin", type="decimal", precision=3, scale=1)
     */
    private $poidsmin;

    /**
     * @var string
     *
     * @ORM\Column(name="poidsmax", type="decimal", precision=3, scale=1)
     */
    private $poidsmax;

    /**
     * @var string
     *
     * @ORM\Column(name="tarif", type="decimal", precision=10)
     */
    private $tarif;

    /**
     * @var string
     *
     * @ORM\Column(name="tva", type="decimal", precision=10, nullable=true)
     */
    private $tva;

    /**
     * @var string
     *
     * @ORM\Column(name="ttc", type="decimal", precision=10, nullable=true)
     */
    private $ttc;

    /**
     * @var string
     *
     * @ORM\Column(name="client", type="string", length=255)
     */
    private $client;


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
     * Set poidsmin
     *
     * @param string $poidsmin
     *
     * @return extra_bareme
     */
    public function setPoidsmin($poidsmin)
    {
        $this->poidsmin = $poidsmin;
    
        return $this;
    }

    /**
     * Get poidsmin
     *
     * @return string
     */
    public function getPoidsmin()
    {
        return $this->poidsmin;
    }

    /**
     * Set poidsmax
     *
     * @param string $poidsmax
     *
     * @return extra_bareme
     */
    public function setPoidsmax($poidsmax)
    {
        $this->poidsmax = $poidsmax;
    
        return $this;
    }

    /**
     * Get poidsmax
     *
     * @return string
     */
    public function getPoidsmax()
    {
        return $this->poidsmax;
    }

    /**
     * Set tarif
     *
     * @param string $tarif
     *
     * @return extra_bareme
     */
    public function setTarif($tarif)
    {
        $this->tarif = $tarif;
    
        return $this;
    }

    /**
     * Get tarif
     *
     * @return string
     */
    public function getTarif()
    {
        return $this->tarif;
    }

    /**
     * Set tva
     *
     * @param string $tva
     *
     * @return extra_bareme
     */
    public function setTva($tva)
    {
        $this->tva = $tva;
    
        return $this;
    }

    /**
     * Get tva
     *
     * @return string
     */
    public function getTva()
    {
        return $this->tva;
    }

    /**
     * Set ttc
     *
     * @param string $ttc
     *
     * @return extra_bareme
     */
    public function setTtc($ttc)
    {
        $this->ttc = $ttc;
    
        return $this;
    }

    /**
     * Get ttc
     *
     * @return string
     */
    public function getTtc()
    {
        return $this->ttc;
    }

    /**
     * Set client
     *
     * @param string $client
     *
     * @return extra_bareme
     */
    public function setClient($client)
    {
        $this->client = $client;
    
        return $this;
    }

    /**
     * Get client
     *
     * @return string
     */
    public function getClient()
    {
        return $this->client;
    }
}

