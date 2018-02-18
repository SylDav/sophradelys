<?php

namespace SophrologieBundle\Controller;

use SophrologieBundle\Entity\Theme;
use SophrologieBundle\Entity\Appointment;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

/**
 * Theme controller.
 *
 */
class ThemeController extends Controller
{
    /**
     * Lists all theme entities.
     *
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();

        $themes = $em->getRepository('SophrologieBundle:Theme')->findAll();

        return $this->render('theme/index.html.twig', array(
            'themes' => $themes,
        ));
    }

    /**
     * Creates a new theme entity.
     *
     */
    public function newAction(Request $request, Appointment $appointment)
    {
        $theme = new Theme();
        $form = $this->createForm('SophrologieBundle\Form\ThemeType', $theme);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();

            foreach ($appointment->getUsers() as $user){
                $theme->setUser($user);
                $user_id = $user->getId();
            }
            $em->persist($theme);

            $appointment->setTheme($theme);
            $appointment->setTitle("Premier rendez-vous");
            $em->persist($appointment);
            $em->flush();

            return $this->redirectToRoute('user_historic', array('id' => $user_id));
        }

        return $this->render('theme/new.html.twig', array(
            'theme' => $theme,
            'appointment' => $appointment,
            'form' => $form->createView(),
        ));
    }

    /**
     * Finds and displays a theme entity.
     *
     */
    public function showAction(Theme $theme)
    {
        return $this->render('theme/show.html.twig', array(
            'theme' => $theme,
        ));
    }

    /**
     * Displays a form to edit an existing theme entity.
     *
     */
    public function editAction(Request $request, Theme $theme)
    {
        $editForm = $this->createForm('SophrologieBundle\Form\ThemeType', $theme);
        $editForm->handleRequest($request);

        if ($editForm->isSubmitted() && $editForm->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('user_historic', array('id' => $theme->getUser()->getId()));
        }

        return $this->render('theme/edit.html.twig', array(
            'theme' => $theme,
            'edit_form' => $editForm->createView(),
        ));
    }

    public function disableAction(Theme $theme)
    {
        $em = $this->getDoctrine()->getManager();
        $theme->setEnabled(0);
        $em->persist($theme);
        $em->flush();

        return $this->redirectToRoute('user_historic', array('id' => $theme->getUser()->getId()));
    }
}
