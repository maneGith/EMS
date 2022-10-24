<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Envoi
 *
 * @ORM\Table(name="envoi")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\EnvoiRepository")
 */
class Envoi
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
     * @ORM\Column(name="codeenvoi", type="string", length=255)
     */
    private $codeenvoi;


    /**
     * @var string
     *
     * @ORM\Column(name="modepaie",type="string", length=255)
     */
    private $modepaie;

    /**
     * @var string
     *
     * @ORM\Column(name="nature",type="string", length=255)
     */
    private $nature;

    /**
     * @var string
     *
     * @ORM\Column(name="codebarre", type="string", length=255)
     */
    private $codebarre;

    /**
     * @var string
     *
     * @ORM\Column(name="valeur", type="string", length=255)
     */
    private $valeur;

    /**
     * @var string
     *
     * @ORM\Column(name="desfacture",type="string", length=255)
     */
    private $desfacture;

    /**
     * @var string
     *
     * @ORM\Column(name="poids", type="decimal", precision=5, scale=3)
     */
    private $poids;

    /**
     * @var string
     *
     * @ORM\Column(name="tarif", type="decimal", precision=10, scale=0)
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
     * @ORM\Column(name="type", type="string", length=255)
     */
    private $type;

    /**
     * @var string
     *
     * @ORM\Column(name="client", type="string", length=255)
     */
    private $client;

    /**
     * @var string
     *
     * @ORM\Column(name="echelle", type="string", length=255)
     */
    private $echelle;

    /**
     * @var text
     *
     * @ORM\Column(name="description", type="text")
     */
    private $description;

    /**
     * @var string
     *
     * @ORM\Column(name="date", type="string", length=255)
     */
    private $date;

    /**
     * @var string
     *
     * @ORM\Column(name="heure", type="string", length=255)
     */
    private $heure;

    /**
     * @var string
     *
     * @ORM\Column(name="etat", type="string", length=255)
     */
    private $etat;

    /**
     * @ORM\ManyToOne(targetEntity="Expediteur", cascade={"persist","remove"})
     *
     * @ORM\JoinColumn(name="expediteur", referencedColumnName="id")
     */
    private $expediteur;

    /**
     * @ORM\ManyToOne(targetEntity="Destinataire", cascade={"persist","remove"})
     *
     * @ORM\JoinColumn(name="destinataire", referencedColumnName="id")
     */
    private $destinataire;

    /**
     * @ORM\ManyToOne(targetEntity="Bordereau")
     *
     * @ORM\JoinColumn(name="bordereau", referencedColumnName="id")
     */
    private $bordereau;

    /**
     * @ORM\ManyToOne(targetEntity="Agence")
     *
     * @ORM\JoinColumn(name="agence", referencedColumnName="id")
     */
    private $agence;



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
     * Set codeenvoi
     *
     * @param string $codeenvoi
     *
     * @return Envoi
     */
    public function setCodeenvoi($codeenvoi)
    {
        $this->codeenvoi = $codeenvoi;

        return $this;
    }

    /**
     * Get codeenvoi
     *
     * @return string
     */
    public function getCodeenvoi()
    {
        return $this->codeenvoi;
    }

    /**
     * Set codebarre
     *
     * @param string $codebarre
     *
     * @return Envoi
     */
    public function setCodebarre($codebarre)
    {
        $this->codebarre = $codebarre;

        return $this;
    }

    /**
     * Get codebarre
     *
     * @return string
     */
    public function getCodebarre()
    {
        return $this->codebarre;
    }

    /**
     * Set poids
     *
     * @param string $poids
     *
     * @return Envoi
     */
    public function setPoids($poids)
    {
        $this->poids = $poids;

        return $this;
    }

    /**
     * Get poids
     *
     * @return string
     */
    public function getPoids()
    {
        return $this->poids;
    }

    /**
     * Set tarif
     *
     * @param string $tarif
     *
     * @return Envoi
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
     * Set type
     *
     * @param string $type
     *
     * @return Envoi
     */
    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Get type
     *
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Set date
     *
     * @param string $date
     *
     * @return Envoi
     */
    public function setDate($date)
    {
        $this->date = $date;

        return $this;
    }

    /**
     * Get date
     *
     * @return string
     */
    public function getDate()
    {
        return $this->date;
    }

    /**
     * Set heure
     *
     * @param string $heure
     *
     * @return Envoi
     */
    public function setHeure($heure)
    {
        $this->heure = $heure;

        return $this;
    }

    /**
     * Get heure
     *
     * @return string
     */
    public function getHeure()
    {
        return $this->heure;
    }

    /**
     * @return mixed
     */
    public function getExpediteur()
    {
        return $this->expediteur;
    }

    /**
     * @param mixed $expediteur
     */
    public function setExpediteur($expediteur)
    {
        $this->expediteur = $expediteur;
    }

    /**
     * @return mixed
     */
    public function getDestinataire()
    {
        return $this->destinataire;
    }

    /**
     * @param mixed $destinataire
     */
    public function setDestinataire($destinataire)
    {
        $this->destinataire = $destinataire;
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
     * @return text
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @param text $description
     */
    public function setDescription($description)
    {
        $this->description = $description;
    }


    /**
     * @return string
     */
    public function getValeur()
    {
        return $this->valeur;
    }

    /**
     * @param string $valeur
     */
    public function setValeur($valeur)
    {
        $this->valeur = $valeur;
    }

    /**
     * @return string
     */
    public function getModepaie()
    {
        return $this->modepaie;
    }

    /**
     * @param string $modepaie
     */
    public function setModepaie($modepaie)
    {
        $this->modepaie = $modepaie;
    }


    /**
     * @return string
     */
    public function getNature()
    {
        return $this->nature;
    }

    /**
     * @param string $nature
     */
    public function setNature($nature)
    {
        $this->nature = $nature;
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
    public function getEchelle()
    {
        return $this->echelle;
    }

    /**
     * @param string $echelle
     */
    public function setEchelle($echelle)
    {
        $this->echelle = $echelle;
    }

    /**
     * @return string
     */
    public function getClient()
    {
        return $this->client;
    }

    /**
     * @param string $client
     */
    public function setClient($client)
    {
        $this->client = $client;
    }

    /**
     * @return mixed
     */
    public function getBordereau()
    {
        return $this->bordereau;
    }

    /**
     * @param mixed $bordereau
     */
    public function setBordereau($bordereau)
    {
        $this->bordereau = $bordereau;
    }

    /**
     * @return mixed
     */
    public function getEtat()
    {
        return $this->etat;
    }

    /**
     * @param mixed $etat
     */
    public function setEtat($etat)
    {
        $this->etat = $etat;
    }

    /**
     * @return string
     */
    public function getDesfacture()
    {
        return $this->desfacture;
    }

    /**
     * @param string $desfacture
     */
    public function setDesfacture($desfacture)
    {
        $this->desfacture = $desfacture;
    }
}

