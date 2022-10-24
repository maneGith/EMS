<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Bareme
 *
 * @ORM\Table(name="bareme")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\BaremeRepository")
 */
class Bareme
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
     * @ORM\Column(name="document", type="string", length=255)
     */
    private $document;

    /**
     * @var string
     *
     * @ORM\Column(name="client", type="string", length=255)
     */
    private $client;

    /**
     * @ORM\ManyToOne(targetEntity="Zone")
     *
     * @ORM\JoinColumn(name="domaine", referencedColumnName="id")
     */
    private $domaine;

    /**
     * @var string
     *
     * @ORM\Column(name="typeclient", type="string", length=255, nullable=true)
     */
    private $typeclient;


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
     * Set poidsmin
     *
     * @param string $poidsmin
     *
     * @return Bareme
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
     * @param float $poidsmax
     *
     * @return Bareme
     */
    public function setPoidsmax($poidsmax)
    {
        $this->poidsmax = $poidsmax;

        return $this;
    }

    /**
     * Get poidsmax
     *
     * @return float
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
     * @return Bareme
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
     * Set document
     *
     * @param string $document
     *
     * @return Bareme
     */
    public function setDocument($document)
    {
        $this->document = $document;

        return $this;
    }

    /**
     * Get document
     *
     * @return string
     */
    public function getDocument()
    {
        return $this->document;
    }

    /**
     * Set client
     *
     * @param string $client
     *
     * @return Bareme
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

    /**
     * Set domaine
     *
     * @param string $domaine
     *
     * @return Bareme
     */
    public function setDomaine($domaine)
    {
        $this->domaine = $domaine;

        return $this;
    }

    /**
     * Get domaine
     *
     * @return string
     */
    public function getDomaine()
    {
        return $this->domaine;
    }

    /**
     * @return string
     */
    public function getTva()
    {
        return $this->tva;
    }

    /**
     * @param string $tva
     */
    public function setTva($tva)
    {
        $this->tva = $tva;
    }

    /**
     * @return string
     */
    public function getTtc()
    {
        return $this->ttc;
    }

    /**
     * @param string $ttc
     */
    public function setTtc($ttc)
    {
        $this->ttc = $ttc;
    }

    /**
     * @return string
     */
    public function getTypeclient()
    {
        return $this->typeclient;
    }

    /**
     * @param string $typeclient
     */
    public function setTypeclient($typeclient)
    {
        $this->typeclient = $typeclient;
    }
}

