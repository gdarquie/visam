<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Discipline
 *
 * @ORM\Table(name="discipline", indexes={@ORM\Index(name="fk_discipline_domaine1_idx", columns={"domaine_id"}), @ORM\Index(name="fk_hesamette_discipline", columns={"hesamette_id"})})
 * @ORM\Entity(repositoryClass="AppBundle\Repository\DisciplineRepository")
 */
class Discipline
{
    /**
     * @var integer
     *
     * @ORM\Column(name="discipline_id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var integer
     *
     * @ORM\Column(name="code", type="integer", nullable=true)
     */
    private $code;

    /**
     * @var string
     *
     * @ORM\Column(name="nom", type="string", length=500, nullable=true)
     */
    private $nom;

    /**
     * @var string
     *
     * @ORM\Column(name="type", type="string", length=45, nullable=true)
     */
    private $type;

    /**
     * @var string
     *
     * @ORM\Column(name="abreviation", type="string", length=255, nullable=true)
     */
    private $abreviation;

    /**
     * @var string
     *
     * @ORM\Column(name="description", type="text", length=65535, nullable=true)
     */
    private $description;


    /**
     * @ORM\ManyToOne(targetEntity="Domaine")
     * @ORM\JoinColumn(name="domaine_id", referencedColumnName="domaine_id", nullable=true)
     */
    private $domaineId;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="timestamp", type="datetime", nullable=false)
     */
    private $timestamp;

    /**
     * @var \AppBundle\Entity\Hesamette
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Hesamette")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="hesamette_id", referencedColumnName="hesamette_id")
     * })
     */
    private $hesamette;

    /**
     * @var \Doctrine\Common\Collections\Collection
     *
     * @ORM\ManyToMany(targetEntity="AppBundle\Entity\Ufr", mappedBy="discipline")
     */
    private $ufr;

    /**
     * @var \Doctrine\Common\Collections\Collection
     *
     * @ORM\ManyToMany(targetEntity="AppBundle\Entity\Labo", inversedBy="discipline")
     * @ORM\JoinTable(name="discipline_has_laboratoire",
     *   joinColumns={
     *     @ORM\JoinColumn(name="discipline_id", referencedColumnName="discipline_id")
     *   },
     *   inverseJoinColumns={
     *     @ORM\JoinColumn(name="laboratoire_id", referencedColumnName="laboratoire_id")
     *   }
     * )
     */
    private $labo;

    /**
     * @var \Doctrine\Common\Collections\Collection
     *
     * @ORM\ManyToMany(targetEntity="AppBundle\Entity\Formation", inversedBy="discipline")
     * @ORM\JoinTable(name="discipline_has_formation",
     *   joinColumns={
     *     @ORM\JoinColumn(name="discipline_id", referencedColumnName="discipline_id")
     *   },
     *   inverseJoinColumns={
     *     @ORM\JoinColumn(name="formation_id", referencedColumnName="formation_id")
     *   }
     * )
     */
    private $formation;

    /**
     * @var \Doctrine\Common\Collections\Collection
     *
     * @ORM\ManyToMany(targetEntity="AppBundle\Entity\Ed", inversedBy="discipline")
     * @ORM\JoinTable(name="discipline_has_ecole_doctorale",
     *   joinColumns={
     *     @ORM\JoinColumn(name="discipline_id", referencedColumnName="discipline_id")
     *   },
     *   inverseJoinColumns={
     *     @ORM\JoinColumn(name="ecole_doctorale_id", referencedColumnName="ecole_doctorale_id")
     *   }
     * )
     */
    private $ed;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->ufr = new \Doctrine\Common\Collections\ArrayCollection();
        $this->labo = new \Doctrine\Common\Collections\ArrayCollection();
        $this->formation = new \Doctrine\Common\Collections\ArrayCollection();
        $this->ed = new \Doctrine\Common\Collections\ArrayCollection();
    }


    /**
     * Set code
     *
     * @param integer $code
     *
     * @return Discipline
     */
    public function setCode($code)
    {
        $this->code = $code;

        return $this;
    }

    /**
     * Get code
     *
     * @return integer
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * Set nom
     *
     * @param string $nom
     *
     * @return Discipline
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
     * Set type
     *
     * @param string $type
     *
     * @return Discipline
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
     * Set abreviation
     *
     * @param string $abreviation
     *
     * @return Discipline
     */
    public function setAbreviation($abreviation)
    {
        $this->abreviation = $abreviation;

        return $this;
    }

    /**
     * Get abreviation
     *
     * @return string
     */
    public function getAbreviation()
    {
        return $this->abreviation;
    }

    /**
     * Set description
     *
     * @param string $description
     *
     * @return Discipline
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
     * Set domaineId
     *
     * @param integer $domaineId
     *
     * @return Discipline
     */
    public function setDomaineId($domaineId)
    {
        $this->domaineId = $domaineId;

        return $this;
    }

    /**
     * Get domaineId
     *
     * @return integer
     */
    public function getDomaineId()
    {
        return $this->domaineId;
    }

    /**
     * Set timestamp
     *
     * @param \DateTime $timestamp
     *
     * @return Discipline
     */
    public function setTimestamp($timestamp)
    {
        $this->timestamp = $timestamp;

        return $this;
    }

    /**
     * Get timestamp
     *
     * @return \DateTime
     */
    public function getTimestamp()
    {
        return $this->timestamp;
    }

    /**
     * Get id
     *
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set hesamette
     *
     * @param \AppBundle\Entity\Hesamette $hesamette
     *
     * @return Discipline
     */
    public function setHesamette(\AppBundle\Entity\Hesamette $hesamette = null)
    {
        $this->hesamette = $hesamette;

        return $this;
    }

    /**
     * Get hesamette
     *
     * @return \AppBundle\Entity\Hesamette
     */
    public function getHesamette()
    {
        return $this->hesamette;
    }

    /**
     * Add ufr
     *
     * @param \AppBundle\Entity\Ufr $ufr
     *
     * @return Discipline
     */
    public function addUfr(\AppBundle\Entity\Ufr $ufr)
    {
        $this->ufr[] = $ufr;

        return $this;
    }

    /**
     * Remove ufr
     *
     * @param \AppBundle\Entity\Ufr $ufr
     */
    public function removeUfr(\AppBundle\Entity\Ufr $ufr)
    {
        $this->ufr->removeElement($ufr);
    }

    /**
     * Get ufr
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getUfr()
    {
        return $this->ufr;
    }

    /**
     * Add labo
     *
     * @param \AppBundle\Entity\Labo $labo
     *
     * @return Discipline
     */
    public function addLabo(\AppBundle\Entity\Labo $labo)
    {
        $this->labo[] = $labo;

        return $this;
    }

    /**
     * Remove labo
     *
     * @param \AppBundle\Entity\Labo $labo
     */
    public function removeLabo(\AppBundle\Entity\Labo $labo)
    {
        $this->labo->removeElement($labo);
    }

    /**
     * Get labo
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getLabo()
    {
        return $this->labo;
    }

    /**
     * Add formation
     *
     * @param \AppBundle\Entity\Formation $formation
     *
     * @return Discipline
     */
    public function addFormation(\AppBundle\Entity\Formation $formation)
    {
        $this->formation[] = $formation;

        return $this;
    }

    /**
     * Remove formation
     *
     * @param \AppBundle\Entity\Formation $formation
     */
    public function removeFormation(\AppBundle\Entity\Formation $formation)
    {
        $this->formation->removeElement($formation);
    }

    /**
     * Get formation
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getFormation()
    {
        return $this->formation;
    }

    /**
     * Add ed
     *
     * @param \AppBundle\Entity\Ed $ed
     *
     * @return Discipline
     */
    public function addEd(\AppBundle\Entity\Ed $ed)
    {
        $this->ed[] = $ed;

        return $this;
    }

    /**
     * Remove ed
     *
     * @param \AppBundle\Entity\Ed $ed
     */
    public function removeEd(\AppBundle\Entity\Ed $ed)
    {
        $this->ed->removeElement($ed);
    }

    /**
     * Get ed
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getEd()
    {
        return $this->ed;
    }

    public function __toString()
    {
        return (string) $this->getNom();
    }

}
