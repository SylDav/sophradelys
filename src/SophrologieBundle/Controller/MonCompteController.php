<?php

namespace SophrologieBundle\Controller;

use SophrologieBundle\Entity\Appointment;
use SophrologieBundle\Entity\Theme;
use SophrologieBundle\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

/**
 * User controller.
 *
 */
class MonCompteController extends Controller
{
    /**
     * Partie personnelle de l'utilisateur
     *
     */
    public function indexAction()
    {

        $em = $this->getDoctrine()->getManager();

        $themes = $em->getRepository('SophrologieBundle:Theme')->findByUser($this->getUser());

        $tableau_past = array();
        $tableau_future = array();

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

        return $this->render('monCompte/index.html.twig', array(
            'user' => $this->getUser(),
            'tableau' => $tableau,
            'themes' => $themes,
        ));
    }


    /*
     * Permet de prendre un rendez-vous selon un thème
     */
    public function takeRDVAction(Appointment $appointment_old, $number_week)
    {
        $em = $this->getDoctrine()->getManager();

        //Supprime tous les rendez-vous passés non associés à un client
        $appointments_to_delete = $em
            ->getRepository('SophrologieBundle:Appointment')
            ->findAppointmentsToDelete(new \DateTime());

        $date = date("Y-m-d");
        $num_week_actual = date('W', strtotime($date)) + $number_week;
        //Permet d'avoir la semaine suivante si le jour actuel est Dimanche
        $today = date('l', strtotime($date));
        if ($today == 'Sunday'){
            $num_week_actual++;
        }

        $year_actual = date('Y', strtotime($date));
        $month_actual = date('n', strtotime($date));

        $week_actual = array();
        for($i = 1; $i <= 365; $i++) {
            $week = date("W", mktime(0, 0, 0, $month_actual, $i, $year_actual));
            if($week == $num_week_actual) {

                for($d = 0; $d < 6; $d++) {
                    $appointments_week[$d][0] = new \DateTime(date("Y-m-d", mktime(0, 0, 0, $month_actual, $i + $d, $year_actual)) . " 00:00:00");
                    if ($appointments_week[$d][0] > $date) {

                        $week_actual[$d] = new \DateTime(date("Y-m-d", mktime(0, 0, 0, $month_actual, $i + $d, $year_actual)) . " 00:00:00");
                        $appointments = $em
                            ->getRepository('SophrologieBundle:Appointment')
                            ->findAllAppointmentsByDay($week_actual[$d]);
                        $nb = 1;
                        foreach ($appointments as $appointment) {
                            $appointments_week[$d][$nb] = $appointment;
                            $nb++;
                        }
                    }
                }
                break;
            }
        }

        $message = (new \Swift_Message('Confirmation de rendez-vous'))
            ->setFrom('sylvain.david.60@gmail.com')
            ->setTo('sylvain.david.60@gmail.com')
            ->setBody(
                $this->renderView(
                    'mail/firstAppointment.html.twig',
                    array(
                        'firstname' => 'Sylvain',
                    )
                ),
                'text/html'
            );

        $mailer = $this->get('mailer');

        $mailer->send($message);

        $spool = $mailer->getTransport()->getSpool();
        $transport = $this->get('swiftmailer.transport.real');

        $spool->flushQueue($transport);

        $schedules = $em->getRepository('SophrologieBundle:Schedule')->findAllOrderByTime();

        return $this->render('monCompte/takeRDV.html.twig', array(
            'appointments_week' => $appointments_week,
            'schedules' => $schedules,
            'number_week' => $number_week,
            'appointment_old' => $appointment_old,
        ));
    }

   /* public function confirmRDVAction(Appointment $appointment, Theme $theme){
        $em = $this->getDoctrine()->getManager();

        $appointment->addUser($this->getUser());
        $appointment->setTheme($theme);
        $em->flush($appointment);

        return $this->redirectToRoute("monCompte_index");
    }*/

    /*
     * Permet à l'utilsateur connecté de voir les paramètres de son compte (s'il est seul dessus)
     */
    public function userAction()
    {

        $em = $this->getDoctrine()->getManager();

        $user=$this->getUser();

        return $this->render('monCompte/user.html.twig', array(
            'user' => $user,
        ));
    }


    public function changePWDAction(Request $request)
    {

        $form = $this->createForm('SophrologieBundle\Form\ChangePWDType');
        $form->handleRequest($request);

        $error = 0;
        if ($form->isSubmitted() && $form->isValid()) {
            $error = 1;
            if ($form["current_password"]->getData() == $this->getUser()->getPassword())
            {
                $error = 2;
                if ($form["new_password"]->getData() == $form["confirm_password"]->getData())
                {
                    $error = 3;
                    $this->getUser()->setPassword($form["new_password"]->getData());
                    $this->getDoctrine()->getManager()->flush();
                    //return $this->redirectToRoute('monCompte_changePWD');
                }
            }

        }

        return $this->render('monCompte/changePWD.html.twig', array(
            'form' => $form->createView(),
            'error' => $error,
        ));
    }


}
