<?php

namespace SophrologieBundle\Controller;

use SophrologieBundle\Entity\Schedule;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

/**
 * Schedule controller.
 *
 */
class ScheduleController extends Controller
{
    /**
     * Lists all schedule entities.
     *
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();

        $schedules = $em->getRepository('SophrologieBundle:Schedule')->findAll();

        return $this->render('schedule/index.html.twig', array(
            'schedules' => $schedules,
        ));
    }

    /**
     * Creates a new schedule entity.
     *
     */
    public function newAction(Request $request)
    {
        $schedule = new Schedule();
        $form = $this->createForm('SophrologieBundle\Form\ScheduleType', $schedule);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($schedule);
            $em->flush();

            return $this->redirectToRoute('schedule_new');
        }

        return $this->render('schedule/new.html.twig', array(
            'schedule' => $schedule,
            'form' => $form->createView(),
        ));
    }

    /**
     * Finds and displays a schedule entity.
     *
     */
    public function showAction(Schedule $schedule)
    {
        $deleteForm = $this->createDeleteForm($schedule);

        return $this->render('schedule/show.html.twig', array(
            'schedule' => $schedule,
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Displays a form to edit an existing schedule entity.
     *
     */
    public function editAction(Request $request, Schedule $schedule)
    {
        $deleteForm = $this->createDeleteForm($schedule);
        $editForm = $this->createForm('SophrologieBundle\Form\ScheduleType', $schedule);
        $editForm->handleRequest($request);

        if ($editForm->isSubmitted() && $editForm->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('schedule_edit', array('id' => $schedule->getId()));
        }

        return $this->render('schedule/edit.html.twig', array(
            'schedule' => $schedule,
            'edit_form' => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Deletes a schedule entity.
     *
     */
    public function deleteAction(Request $request, Schedule $schedule)
    {
        $form = $this->createDeleteForm($schedule);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->remove($schedule);
            $em->flush();
        }

        return $this->redirectToRoute('schedule_index');
    }

    /**
     * Creates a form to delete a schedule entity.
     *
     * @param Schedule $schedule The schedule entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm(Schedule $schedule)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('schedule_delete', array('id' => $schedule->getId())))
            ->setMethod('DELETE')
            ->getForm()
        ;
    }
}
