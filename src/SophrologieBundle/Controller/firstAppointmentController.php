<?php

namespace SophrologieBundle\Controller;

use SophrologieBundle\Entity\Appointment;
use SophrologieBundle\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

/**
 * firstAppointment controller.
 *
 */
class firstAppointmentController extends Controller
{
    /**
     * Lists all appointment entities.
     *
     */
    public function indexAction($number_week)
    {
        $em = $this->getDoctrine()->getManager();

        //Supprime tous les rendez-vous passés non associés à un client
        $appointments_to_delete = $em
            ->getRepository('SophrologieBundle:Appointment')
            ->findAppointmentsToDelete(new \DateTime());

        $appointments_week = $this->getAppointmentsByWeek($number_week);


        $schedules = $em->getRepository('SophrologieBundle:Schedule')->findAllOrderByTime();

        return $this->render('firstAppointment/index.html.twig', array(
            'appointments_week' => $appointments_week,
            'schedules' => $schedules,
            'number_week' => $number_week,
        ));
    }

    /**
     * Form to confirm the new user
     *
     */
    public function confirmAction(Request $request, Appointment $appointment)
    {
        $form = $this->createForm('SophrologieBundle\Form\UserAppointmentType');
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $user = new User();
            $user->setName($form["name"]->getData());
            $user->setFirstname($form["firstname"]->getData());
            $user->setMail($form["mail"]->getData());
            $user->setPhonenumber($form["phonenumber"]->getData());
            $em = $this->getDoctrine()->getManager();
            $em->persist($user);
            $em->flush();

            $appointment->addUser($user);
            $em->persist($appointment);
            $em->flush();

            // On supprime les rendez-vous autour de celui pris
            $this->removeAppointmentsAround($appointment);

            //Send mail

            $message = (new \Swift_Message('Confirmation de rendez-vous'))
                ->setFrom('sylvain.david.60@gmail.com')
                ->setTo('sylvain.david.60@gmail.com')
                ->setBody(
                    $this->renderView(
                        'mail/firstAppointment.html.twig',
                        array(
                            'firstname' => $form["firstname"]->getData(),
                            'appointment' => $appointment,
                        )
                    ),
                    'text/html'
                );

            $this->get('mailer')->send($message);



            if ($this->isGranted('ROLE_USER')){
                return $this->redirectToRoute('appointment_index');
            }
            else{
                return $this->redirectToRoute('firstAppointment_index');
            }

        }

        return $this->render('firstAppointment/confirm.html.twig', array(
            'appointment' => $appointment,
            'form' => $form->createView(),
        ));
    }




    /*
     * Functions used in this Controller
     */


    /*
     * Function getting appointments by week
     */

    public function getAppointmentsByWeek($number_week)
    {
        $em = $this->getDoctrine()->getManager();


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

        return $appointments_week;
    }


    /*
     * Delete appointments arount the appointment taken
     */

    public function removeAppointmentsAround($appointment_new){
        $em = $this->getDoctrine()->getManager();

        $interval_new = new \DateInterval('PT30M');
        $last_appointment_new = $em->getRepository('SophrologieBundle:Appointment')->findOneByDate($appointment_new->getDate()->sub($interval_new));
        $next_appointment_new = $em->getRepository('SophrologieBundle:Appointment')->findOneByDate($appointment_new->getDate()->add($interval_new)->add($interval_new));

        if ($last_appointment_new != null){
            if ($last_appointment_new->getUsers()->count() == 0){
                $em->remove($last_appointment_new);
                $em->flush();
            }
        }
        if ($next_appointment_new != null){
            if ($next_appointment_new->getUsers()->count() == 0){
                $em->remove($next_appointment_new);
                $em->flush();
            }
        }

    }
}
