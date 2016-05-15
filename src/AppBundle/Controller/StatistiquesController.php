<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;

use AppBundle\Entity\Ed;
use AppBundle\Entity\Etablissement;
use AppBundle\Entity\Formation;

class StatistiquesController extends Controller
{
    /**
     * @Route("/stats", name="statistiques")
     */
    public function indexAction(Request $request)
    {

    	$em = $this->getDoctrine()->getManager();
    	$eds = $em->getRepository('AppBundle:Ed')->findAll();
    	$etabs = $em->getRepository('AppBundle:Etablissement')->findAll();
        $formations = $em->getRepository('AppBundle:Formation')->findAll();



        //Test pour faire des requêtes en SQL avec Symfony

        //calcul du nombre d'étudiant
        // récupérer la valeur d'une requête SQL qui sera exploitée dans nbEtud
        //SELECT SUM(`etudiants`) AS nb FROM etablissement

        $query = $em->createQuery(
            'SELECT p
            FROM AppBundle:Etablissement p
            WHERE p.etudiants > :etudiants'
        )->setParameter('etudiants', '60000');

        //$nbEtud = $query->setMaxResults(1)->getOneOrNullResult();


        $nbEtud = 125464;

        //calculer la répartition des niveaux dans les formations
        return $this->render('stats.html.twig', array(
        	'eds' => $eds,
        	'etabs' => $etabs,
            'nbEtud' => $nbEtud,
            'formations' => $formations,
        	));
    }


} // Fin de la class DefaultController


