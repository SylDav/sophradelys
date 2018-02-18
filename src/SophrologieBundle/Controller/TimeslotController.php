<?php

namespace SophrologieBundle\Controller;

use SophrologieBundle\Entity\Timeslot;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

/**
 * Timeslot controller.
 *
 */
class TimeslotController extends Controller
{
    /**
     * Lists all timeslot entities.
     *
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();

        $timeslots = $em->getRepository('SophrologieBundle:Timeslot')->findAll();

        return $this->render('timeslot/index.html.twig', array(
            'timeslots' => $timeslots,
        ));
    }

    /**
     * Creates a new timeslot entity.
     *
     */
    public function newAction(Request $request)
    {
        $timeslot = new Timeslot();
        $form = $this->createForm('SophrologieBundle\Form\TimeslotType', $timeslot);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($timeslot);
            $em->flush();

            return $this->redirectToRoute('timeslot_show', array('id' => $timeslot->getId()));
        }

        return $this->render('timeslot/new.html.twig', array(
            'timeslot' => $timeslot,
            'form' => $form->createView(),
        ));
    }

    /**
     * Finds and displays a timeslot entity.
     *
     */
    public function showAction(Timeslot $timeslot)
    {
        $deleteForm = $this->createDeleteForm($timeslot);

        return $this->render('timeslot/show.html.twig', array(
            'timeslot' => $timeslot,
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Displays a form to edit an existing timeslot entity.
     *
     */
    public function editAction(Request $request, Timeslot $timeslot)
    {
        $deleteForm = $this->createDeleteForm($timeslot);
        $editForm = $this->createForm('SophrologieBundle\Form\TimeslotType', $timeslot);
        $editForm->handleRequest($request);

        if ($editForm->isSubmitted() && $editForm->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('timeslot_edit', array('id' => $timeslot->getId()));
        }

        return $this->render('timeslot/edit.html.twig', array(
            'timeslot' => $timeslot,
            'edit_form' => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Deletes a timeslot entity.
     *
     */
    public function deleteAction(Request $request, Timeslot $timeslot)
    {
        $form = $this->createDeleteForm($timeslot);
        $form->handleRequest($request);
        $em = $this->getDoctrine()->getManager();
        $em->remove($timeslot);
        $em->flush();

        return $this->redirectToRoute('timeslot_index');
    }

    /**
     * Creates a form to delete a timeslot entity.
     *
     * @param Timeslot $timeslot The timeslot entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm(Timeslot $timeslot)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('timeslot_delete', array('id' => $timeslot->getId())))
            ->setMethod('DELETE')
            ->getForm()
        ;
    }
}
