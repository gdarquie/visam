<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Axe
 *
 * @ORM\Table(name="axe")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\AxeRepository")
 */
class Axe
{

    /**
     * @var integer
     *
     * @ORM\Column(name="axe_id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $axeId;

    /**
     * @var string
     *
     * @ORM\Column(name="nom", type="text", length=65535, nullable=true)
     */
    private $nom;

    /**
     * @var string
     *
     * @ORM\Column(name="description", type="text", length=65535, nullable=true)
     */
    private $description;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="date_creation", type="datetime", nullable=false)
     */
    private $date_creation;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="last_update", type="datetime", nullable=false)
     */
    private $last_update;

    /**
     * @var \Doctrine\Common\Collections\Collection
     *
     * @ORM\ManyToMany(targetEntity="AppBundle\Entity\Labo", mappedBy="axes", cascade= {"persist"})
     */
    private $labo;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->last_update = new \DateTime();
        $this->date_creation = new \DateTime();
    }


    /**
     * Set nom
     *
     * @param string $nom
     *
     * @return Axe
     */
    public function setNom($nom)
    {
        $this->nom = $nom;

        return $this;
    }

    /**
     * Get nom
     *
     * @return string
     */
    public function getNom()
    {
        return $this->nom;
    }

    /**
     * Set description
     *
     * @param string $description
     *
     * @return Axe
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Get description
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @return \DateTime
     */
    public function getDateCreation()
    {
        return $this->date_creation;
    }

    /**
     * @param \DateTime $date_creation
     */
    public function setDateCreation($date_creation)
    {
        $this->date_creation = $date_creation;
    }

    /**
     * @return \DateTime
     */
    public function getLastUpdate()
    {
        return $this->last_update;
    }

    /**
     * @param \DateTime $last_update
     */
    public function setLastUpdate($last_update)
    {
        $this->last_update = $last_update;
    }

    /**
     * Get axeId
     *
     * @return integer
     */
    public function getAxeId()
    {
        return $this->axeId;
    }

    /**
     * Set labo
     *
     * @param \AppBundle\Entity\Labo $labo
     *
     * @return Axe
     */
    public function setLabo(\AppBundle\Entity\Labo $labo = null)
    {
        $this->labo = $labo;

        return $this;
    }

    /**
     * Get labo
     *
     * @return \AppBundle\Entity\Labo
     */
    public function getLabo()
    {
        return $this->labo;
    }

    public function __toString()
    {
        return (string) $this->getNom();
    }


}

