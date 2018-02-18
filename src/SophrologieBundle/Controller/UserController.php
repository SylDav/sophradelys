<?php

namespace SophrologieBundle\Controller;

use SophrologieBundle\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

/**
 * User controller.
 *
 */
class UserController extends Controller
{
    /**
     * Lists all user entities if $initial = "all".
     * If $initial = a letter of alphabet :
     * Lists all user entities beginning by $initial.
     *
     */
    public function indexAction($initial)
    {
        $em = $this->getDoctrine()->getManager();

        if ($initial == "all"){
            $users = $em->getRepository('SophrologieBundle:User')->findAllUsersOrderByNameAndFirstname();
        }
        else {
            $users = $em->getRepository('SophrologieBundle:User')->findAllUsersOrderByNameAndFirstnameStartingByLetter($initial);
        }
        $alphabet = array();
        foreach (range('A', 'Z') as $letter){
            $alphabet[] = $letter;
        }

        return $this->render('user/index.html.twig', array(
            'users' => $users,
            'alphabet' => $alphabet,
        ));
    }

    /**
     * Creates a new user entity.
     *
     */
    public function newAction(Request $request)
    {
        $user = new User();
        $form = $this->createForm('SophrologieBundle\Form\UserType', $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($user);
            $em->flush();

            return $this->redirectToRoute('user_index');
        }

        return $this->render('user/new.html.twig', array(
            'user' => $user,
            'form' => $form->createView(),
        ));
    }

    /**
     * Finds and displays a user entity.
     *
     */
    public function showAction(User $user)
    {
        $deleteForm = $this->createDeleteForm($user);

        return $this->render('user/show.html.twig', array(
            'user' => $user,
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Displays a form to edit an existing user entity.
     *
     */
    public function editAction(Request $request, User $user)
    {
        $deleteForm = $this->createDeleteForm($user);
        $editForm = $this->createForm('SophrologieBundle\Form\UserType', $user);
        $editForm->handleRequest($request);

        if ($editForm->isSubmitted() && $editForm->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('user_edit', array('id' => $user->getId()));
        }

        return $this->render('user/edit.html.twig', array(
            'user' => $user,
            'edit_form' => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Deletes a user entity.
     *
     */
    public function deleteAction(User $user)
    {
        $em = $this->getDoctrine()->getManager();
        $em->remove($user);
        $em->flush();

        return $this->redirectToRoute('user_index');
    }

    /**
     * Creates a form to delete a user entity.
     *
     * @param User $user The user entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm(User $user)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('user_delete', array('id' => $user->getId())))
            ->setMethod('DELETE')
            ->getForm()
        ;
    }


    /**
     * Finds and displays a user historic.
     *
     */
    public function historicAction(User $user)
    {
        $em = $this->getDoctrine()->getManager();

        $themes = $em->getRepository('SophrologieBundle:Theme')->findByUser($user);

        $tableau = array();

        $num_theme = 0;
        foreach ($themes as $theme){
            $num_appointment = 0;
            $appointments = $em->getRepository('SophrologieBundle:Appointment')->findAllAppointmentsByThemeAndOrderByDate($theme);
            foreach ($appointments as $appointment){
                $tableau[$num_theme][$num_appointment] = $appointment;
                $num_appointment++;
            }
            $num_theme++;
        }

        return $this->render('user/historic.html.twig', array(
            'user' => $user,
            'tableau' => $tableau,
        ));
    }

    /*
     * Confirme un utilisateur (on lui crée un username et un mdp)
     */

    public function confirmAction(User $user){
        $em = $this->getDoctrine()->getManager();

        $username = strtolower(substr($user->getFirstname(), 0, 1) . $user->getName());

        $username_count = $em->getRepository('SophrologieBundle:User')->countAllUserByUsername($username);
        if ($username_count){
            if ($username_count > 0){
                $username .= strval($username_count);
            }
        }

        $user->setUsername($username);
        $user->setPassword('Test123');
        $role = $em
            ->getRepository('SophrologieBundle:Role')
            ->findOneById(2);

        $user->addRole($role);

        $em->persist($user);
        $em->flush();

        $em->persist($user);
        $em->flush();


        return $this->redirectToRoute('user_show', array('id' => $user->getId()));
    }
}
