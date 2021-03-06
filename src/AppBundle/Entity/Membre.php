<?php

namespace AppBundle\Entity;

use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\ORM\Mapping as ORM;

/**
 * Membre
 *
 * @ORM\Table(name="participant")
 * @ORM\Entity
 */
class Membre
{

    /**
     * @var integer
     *
     * @ORM\Column(name="participant_id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $membre_id;

    /**
     * @var string
     *
     * @ORM\Column(name="nom", type="string", length=500, nullable=true)
     */
    private $nom;

    /**
     * @var string
     *
     * @ORM\Column(name="prenom", type="string", length=500, nullable=true)
     */
    private $prenom;

    /**
     * @var string
     *
     * @ORM\Column(name="profession", type="string", length=500, nullable=true)
     */
    private $profession;

    /**
     * @var string
     *
     * @ORM\Column(name="genre", type="string", length=1, nullable=true)
     */
    private $genre;

    /**
     * @var string
     *
     * @ORM\Column(name="mail", type="string", length=500, nullable=true)
     *
     * @Assert\Email(
     *     message = "L'email '{{ value }}' n'est pas une adresse email valide.",
     *     checkMX = true
     * )
     */
    private $mail;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="date_creation", type="datetime")
     */
    private $date_creation;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="last_update", type="datetime")
     */
    private $last_update;

    /**
     * @var \Doctrine\Common\Collections\Collection
     *
     * @ORM\ManyToMany(targetEntity="AppBundle\Entity\Labo", mappedBy="membre")
     */
    private $labo;

    /**
     * @var \Doctrine\Common\Collections\Collection
     *
     * @ORM\ManyToMany(targetEntity="AppBundle\Entity\Formation", mappedBy="membre")
     */
    private $formation;


    /**
     * @var \Doctrine\Common\Collections\Collection
     *
     * @ORM\ManyToMany(targetEntity="AppBundle\Entity\Axe", inversedBy="membres")
     * @ORM\JoinTable(name="participant_has_axe",
     *   joinColumns={
     *     @ORM\JoinColumn(name="participant_id", referencedColumnName="participant_id")
     *   },
     *   inverseJoinColumns={
     *     @ORM\JoinColumn(name="axe_id", referencedColumnName="axe_id")
     *   }
     * )
     */
    private $axe;


    /**
     * @var \Doctrine\Common\Collections\Collection
     *
     * @ORM\ManyToMany(targetEntity="AppBundle\Entity\Ed", inversedBy="membres")
     * @ORM\JoinTable(name="participant_has_ecole_doctorale",
     *   joinColumns={
     *     @ORM\JoinColumn(name="participant_id", referencedColumnName="participant_id")
     *   },
     *   inverseJoinColumns={
     *     @ORM\JoinColumn(name="ecole_doctorale_id", referencedColumnName="ecole_doctorale_id")
     *   }
     * )
     */
    private $ed;

    /**
     * @var \Doctrine\Common\Collections\Collection
     *
     * @ORM\ManyToMany(targetEntity="AppBundle\Entity\Tag", inversedBy="tag")
     * @ORM\JoinTable(name="participant_has_tag",
     *   joinColumns={
     *     @ORM\JoinColumn(name="participant_id", referencedColumnName="participant_id")
     *   },
     *   inverseJoinColumns={
     *     @ORM\JoinColumn(name="tag_id", referencedColumnName="tag_id")
     *   }
     * )
     */
    private $tag;


    /**
     * Constructor
     */
    public function __construct()
    {
        $this->date_creation = new \DateTime();
        $this->last_update = new \DateTime();
        $this->labo = new \Doctrine\Common\Collections\ArrayCollection();
        $this->formation = new \Doctrine\Common\Collections\ArrayCollection();
        $this->ed = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * @return int
     */
    public function getMembreId()
    {
        return $this->membre_id;
    }

    /**
     * @param int $membre_id
     */
    public function setMembreId($membre_id)
    {
        $this->membre_id = $membre_id;
    }

    /**
     * @return string
     */
    public function getNom()
    {
        return $this->nom;
    }

    /**
     * @param string $nom
     */
    public function setNom($nom)
    {
        $this->nom = $nom;
    }

    /**
     * @return string
     */
    public function getPrenom()
    {
        return $this->prenom;
    }

    /**
     * @param string $prenom
     */
    public function setPrenom($prenom)
    {
        $this->prenom = $prenom;
    }

    /**
     * @return string
     */
    public function getProfession()
    {
        return $this->profession;
    }

    /**
     * @param string $profession
     */
    public function setProfession($profession)
    {
        $this->profession = $profession;
    }

    /**
     * @return string
     */
    public function getGenre()
    {
        return $this->genre;
    }

    /**
     * @param string $genre
     */
    public function setGenre($genre)
    {
        $this->genre = $genre;
    }

    /**
     * @return string
     */
    public function getMail()
    {
        return $this->mail;
    }

    /**
     * @param string $mail
     */
    public function setMail($mail)
    {
        $this->mail = $mail;
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
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getLabo()
    {
        return $this->labo;
    }

    /**
     * @param \Doctrine\Common\Collections\Collection $labo
     */
    public function setLabo($labo)
    {
        $this->labo = $labo;
    }

    /**
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getFormation()
    {
        return $this->formation;
    }

    /**
     * @param \Doctrine\Common\Collections\Collection $formation
     */
    public function setFormation($formation)
    {
        $this->formation = $formation;
    }

    /**
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getAxe()
    {
        return $this->axe;
    }

    /**
     * @param \Doctrine\Common\Collections\Collection $axe
     */
    public function setAxe($axe)
    {
        $this->axe = $axe;
    }

    /**
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getEd()
    {
        return $this->ed;
    }

    /**
     * @param \Doctrine\Common\Collections\Collection $ed
     */
    public function setEd($ed)
    {
        $this->ed = $ed;
    }

    /**
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getTag()
    {
        return $this->tag;
    }

    /**
     * @param \Doctrine\Common\Collections\Collection $tag
     */
    public function setTag($tag)
    {
        $this->tag = $tag;
    }

    public function __toString()
    {
        return (string) $this->getNom();
    }



}
