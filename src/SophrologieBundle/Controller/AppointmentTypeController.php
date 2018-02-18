<?php

namespace SophrologieBundle\Controller;

use SophrologieBundle\Entity\AppointmentType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

/**
 * Appointmenttype controller.
 *
 */
class AppointmentTypeController extends Controller
{
    /**
     * Lists all appointmentType entities.
     *
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();

        $appointmentTypes = $em->getRepository('SophrologieBundle:AppointmentType')->findAll();

        return $this->render('appointmenttype/index.html.twig', array(
            'appointmentTypes' => $appointmentTypes,
        ));
    }

    /**
     * Creates a new appointmentType entity.
     *
     */
    public function newAction(Request $request)
    {
        $appointmentType = new Appointmenttype();
        $form = $this->createForm('SophrologieBundle\Form\AppointmentTypeType', $appointmentType);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($appointmentType);
            $em->flush();

            return $this->redirectToRoute('appointmenttype_show', array('id' => $appointmentType->getId()));
        }

        return $this->render('appointmenttype/new.html.twig', array(
            'appointmentType' => $appointmentType,
            'form' => $form->createView(),
        ));
    }

    /**
     * Finds and displays a appointmentType entity.
     *
     */
    public function showAction(AppointmentType $appointmentType)
    {
        $deleteForm = $this->createDeleteForm($appointmentType);

        return $this->render('appointmenttype/show.html.twig', array(
            'appointmentType' => $appointmentType,
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Displays a form to edit an existing appointmentType entity.
     *
     */
    public function editAction(Request $request, AppointmentType $appointmentType)
    {
        $deleteForm = $this->createDeleteForm($appointmentType);
        $editForm = $this->createForm('SophrologieBundle\Form\AppointmentTypeType', $appointmentType);
        $editForm->handleRequest($request);

        if ($editForm->isSubmitted() && $editForm->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('appointmenttype_edit', array('id' => $appointmentType->getId()));
        }

        return $this->render('appointmenttype/edit.html.twig', array(
            'appointmentType' => $appointmentType,
            'edit_form' => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Deletes a appointmentType entity.
     *
     */
    public function deleteAction(Request $request, AppointmentType $appointmentType)
    {
        $form = $this->createDeleteForm($appointmentType);
        $form->handleRequest($request);
        $em = $this->getDoctrine()->getManager();
        $em->remove($appointmentType);
        $em->flush();

        return $this->redirectToRoute('appointmenttype_index');
    }

    /**
     * Creates a form to delete a appointmentType entity.
     *
     * @param AppointmentType $appointmentType The appointmentType entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm(AppointmentType $appointmentType)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('appointmenttype_delete', array('id' => $appointmentType->getId())))
            ->setMethod('DELETE')
            ->getForm()
        ;
    }
}
