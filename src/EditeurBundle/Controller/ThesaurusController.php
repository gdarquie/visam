<?php

namespace EditeurBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\Request;

use AppBundle\Entity\Thesaurus;
use EditeurBundle\Form\LaboType;

/**
 *
 * @Route("/editeur/thesaurus")
 */
class ThesaurusController extends Controller
{
    /**
     * Créer un thesaurus
     *
     * @Route("/new", name="editeur_thesaurus_new")
     */
    public function newLaboAction(Request $request){

        $thesaurus = new Thesaurus();
        $editForm = $this->createForm('EditeurBundle\Form\ThesaurusType', $thesaurus);
        $editForm->handleRequest($request);


        if ($editForm->isSubmitted() && $editForm->isValid()) {

            $thesaurus = $editForm->getData();
            $em = $this->getDoctrine()->getManager();

            $now = new \DateTime();
            $thesaurus->setDateCreation($now);
            $thesaurus->setLastUpdate($now);

            $em->persist($thesaurus);
            $em->flush();

            return $this->redirectToRoute('editeur');
        }

        return $this->render('EditeurBundle:Thesaurus:new.html.twig', array(
            'edit_form' => $editForm->createView(),
        ));
    }

    /**
     * Editer un itme du thesaurus
     *
     * @Route("/{id}/edit", name="editeur_thesaurus_edit")
     */
    public function editThesaurusAction(Request $request, Thesaurus $thesaurus){

        $deleteForm = $this->createDeleteForm($thesaurus);

        $editForm = $this->createForm('EditeurBundle\Form\ThesaurusType', $thesaurus);
        $editForm->handleRequest($request);

        if ($editForm->isSubmitted() && $editForm->isValid()) {

            $thesaurus = $editForm->getData();
            $em = $this->getDoctrine()->getManager();

            $now = new \DateTime();
            $thesaurus->setLastUpdate($now);

            $em->persist($thesaurus);
            $em->flush();

            return $this->redirectToRoute('thesaurus', array('id' => $thesaurus->getThesaurusId() ));
        }

        return $this->render('EditeurBundle:Thesaurus:edit.html.twig', array(
            'thesaurus' => $thesaurus,
            'edit_form' => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Effacer un item
     *
     * @Route("/{id}/delete", name="editeur_thesaurus_delete")
     * @Method("DELETE")
     */
    public function deleteAction(Request $request, Thesaurus $thesaurus)
    {
        $form = $this->createDeleteForm($thesaurus);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->remove($thesaurus);
            $em->flush();
        }

        return $this->redirectToRoute('editeur');
    }

    /**
     * Créer un form pour effacer un item
     *
     * @param Labo $labo
     *
     * @return \Symfony\Component\Form\Form
     */
    private function createDeleteForm(Thesaurus $thesaurus)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('editeur_thesaurus_delete', array('id' => $thesaurus->getThesaurusId())))
            ->setMethod('DELETE')
            ->getForm()
            ;
    }
}
