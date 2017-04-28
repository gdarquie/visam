<?php

namespace AppBundle\Repository;

use Doctrine\ORM\EntityRepository;

class DisciplineRepository extends EntityRepository
{

    public function createAlphabeticalQueryBuilder()
    {
        return $this->createQueryBuilder('discipline')
            ->orderBy('discipline.nom', 'ASC');
    }

    public function createAlphabeticalQueryBuilderByType($type)
    {
        return $this->createQueryBuilder('discipline')
            ->where('discipline.type = :type')
            ->orderBy('discipline.nom', 'ASC')
            ->setParameter('type', $type);
    }

    /***
     * @param $value
     * @return boolean
     *
     */
    public function verifyDisciplineByAbreviation($value, $type)
    {
        $discipline = $this
            ->findOneBy(array('discipline' => $value, 'type' => $type));

        if (!$discipline) {
            return false;
        }
        return true;
    }


    /***
     * @param $value
     * @return boolean
     *
     */
    public function verifyDisciplineByNom($value, $type){

        $discipline = $this
            ->findOneBy(array('nom' => $value, 'type' => $type));

        if (!$discipline) {
            return false;
        }
        return true;
    }

    public function findAllDisciplines($type){
        $qb = $this->createQueryBuiler('d')->select('d.nom, d.abreviation')->where('d.type = :type')->setParameter('type', $type);
        $query = $qb->getQuery()->getArrayResult();
        return $query;
    }


}