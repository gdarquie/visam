<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 28/04/2017
 * Time: 16:00
 */

namespace AppBundle\Repository;

use Doctrine\ORM\EntityRepository;
use AppBundle\Entity\Formation;

class LocalisationRepository extends EntityRepository
{
    public function findAllLocalisationsFormation($id)
    {

        $qb = $this->createQueryBuilder('l')
            ->leftJoin('l.formation', 'f')
            ->where('f.id = :formation')
            ->setParameter('formation', $id);

        $query = $qb->getQuery()->getArrayResult();

        return $query;
    }

    public function findAllLocalisationsLabo($id)
    {

        $qb = $this->createQueryBuilder('l')
            ->leftJoin('l.labo', 'f')
            ->where('f.id = :labo')
            ->setParameter('labo', $id);

        $query = $qb->getQuery()->getArrayResult();

        return $query;
    }

    public function findEtablissementLocalisations($etablissementId)
    {

        $qb = $this->createQueryBuilder('l')
            ->leftJoin('l.etablissement', 'e')
            ->where('e.etablissementId = :etab')
            ->setParameter('etab', $etablissementId);

        $query = $qb->getQuery()->getArrayResult();

        return $query;
    }

    public function existLocalisation($adresse, $code, $ville){

        $localisation = $this
            ->findBy(array('adresse' => $adresse, 'code' => $code, 'ville' => $ville));

        if (!$localisation || count($localisation) < 1) {
            return false;
        }
        return true;
    }
}
