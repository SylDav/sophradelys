<?php

namespace SophrologieBundle\Controller;

use SophrologieBundle\Entity\Appointment;
use SophrologieBundle\Entity\Theme;
use SophrologieBundle\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

/**
 * Appointment controller.
 *
 */
class AppointmentController extends Controller
{
    /**
     * Lists all appointment entities.
     *
     */
    public function indexAction($number_week)
    {
        $em = $this->getDoctrine()->getManager();

        $appointments_week = $this->getAppointmentsByWeek($number_week);

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

        return $this->render('appointment/index.html.twig', array(
            'appointments_week' => $appointments_week,
            'schedules' => $schedules,
            'number_week' => $number_week,
        ));
    }

    /**
     * Creates a multiple new appointment entity.
     *
     */
    public function newAction(Request $request)
    {
        $form = $this->createForm('SophrologieBundle\Form\AddAppointmentType');
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();

            $day_form = $form["day"]->getData();
            list($day, $month, $year) = explode('/', $day_form);
            $day_form = $month . '/' . $day . '/' . $year;

            $begin_form = $form["begin"]->getData();
            $end_form = $form["end"]->getData();

            $begin = new \DateTime($day_form . ' ' . $begin_form . ':00');
            $end = new \DateTime($day_form . ' ' . $end_form . ':00');
            //Réduit l'intervalle de fin pour ne pas créer l'avant dernier RDV pour n'avoir que des RDV d'une heure
            $interval = new \DateInterval('PT30M');
            $end->sub($interval);

            /*$intervalle = new \DateInterval('PT30M');
            $begin->add(new \DateInterval('PT30M'));*/

            foreach (new \DatePeriod($begin, new \DateInterval('PT30M'), $end) as $new_begin)
            {
                $appointment = new Appointment;
                $appointment->setDate($new_begin);
                $em->persist($appointment);
                $em->flush();
            }



            return $this->redirectToRoute('appointment_admin');
        }

        return $this->render('appointment/new.html.twig', array(
            'form' => $form->createView(),
        ));
    }

    /**
     * Finds and displays a appointment entity.
     *
     */
    public function showAction(Appointment $appointment)
    {

        return $this->render('appointment/show.html.twig', array(
            'appointment' => $appointment,
        ));
    }

    /**
     * Displays a form to edit an existing appointment entity.
     *
     */
    public function editAction(Request $request, Appointment $appointment)
    {

        if ($appointment->getDate() != null){
            $editForm = $this->createForm('SophrologieBundle\Form\AppointmentType', $appointment);
        }else{
            $editForm = $this->createForm('SophrologieBundle\Form\SeanceType', $appointment);
        }
        $editForm->handleRequest($request);

        if ($editForm->isSubmitted() && $editForm->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('appointment_admin');
        }

        return $this->render('appointment/edit.html.twig', array(
            'appointment' => $appointment,
            'edit_form' => $editForm->createView(),
        ));
    }

    /**
     * Deletes a appointment entity.
     *
     */
    public function deleteAction(Appointment $appointment)
    {
        if ($appointment->getUsers()->count() > 0){
            foreach ($appointment->getUsers() as $user){
                $appointment->removeUser($user);
            }
        }
        else {
            $em = $this->getDoctrine()->getManager();
            $em->remove($appointment);
            $em->flush();
        }


        return $this->redirectToRoute('appointment_admin');
    }

    public function adminAction($number_week)
    {
        if (!$this->isGranted('ROLE_ADMIN') AND $this->isGranted('ROLE_USER')){
            return $this->redirectToRoute('monCompte_index');
        }

        $em = $this->getDoctrine()->getManager();

        //Supprime tous les rendez-vous passés non associés à un client
        $appointments_to_delete = $em
            ->getRepository('SophrologieBundle:Appointment')
            ->findAppointmentsToDelete(new \DateTime());

        $appointments_week = $this->getAppointmentsByWeek($number_week);

        $date = date("Y-m-d");
        $num_week_actual = date('W', strtotime($date)) + $number_week;
        $year_actual = date('Y', strtotime($date));
        $month_actual = date('n', strtotime($date));
        //Permet d'avoir la semaine suivante si le jour actuel est Dimanche
        $today = date('l', strtotime($date));
        if ($today == 'Sunday'){
            $num_week_actual++;
        }




        $week_actual = array();
        $nb_rdv_for_message = 0;
        for($i = 1; $i <= 365; $i++) {
            $week = date("W", mktime(0, 0, 0, $month_actual, $i, $year_actual));
            if($week == $num_week_actual) {

                for($d = 0; $d < 6; $d++) {
                    $appointments_week[$d][0] = new \DateTime(date("Y-m-d", mktime(0, 0, 0, $month_actual, $i+$d, $year_actual)) . " 00:00:00");
                    $week_actual[$d] = new \DateTime(date("Y-m-d", mktime(0, 0, 0, $month_actual, $i+$d, $year_actual)) . " 00:00:00");
                    $appointments = $em
                        ->getRepository('SophrologieBundle:Appointment')
                        ->findAllAppointmentsByDay($week_actual[$d]);
                    $nb = 1;
                    foreach ($appointments as $appointment){
                        $appointments_week[$d][$nb] = $appointment;
                        $nb++;
                        if ($appointment->getUsers()->count() > 0){
                            $nb_rdv_for_message++;
                        }
                    }
                }
                break;
            }
        }
        if ($nb_rdv_for_message == 0){
            $message = "Il n'y a pas encore de rendez-vous... Mais ça ne va pas tarder ! Profites-en pour peaufiner tes séances...
                    et pour faire des bisous à ton chéri ;)";
        }elseif ($nb_rdv_for_message < 5){
            $message = "Quelques clients ! C'est le début de la richesse !!! Prends soin d'eux :)";
        }elseif ($nb_rdv_for_message < 10){
            $message = "Eh beh ! Tu deviens carrément famous mon amour ! :O Tes efforts sont récompensés ;) <3";
        }else{
            $message = "Polalalala ! Carrément overbooked ma chérie !!! :D Tu peux être fière de toi mon amour ;) <3 <3";
        }

        $schedules = $em->getRepository('SophrologieBundle:Schedule')->findAllOrderByTime();
        //$numberApp = $em->getRepository('SophrologieBundle:Appointment')->findAllAppointmentsOfTheWeekCount();

        return $this->render('appointment/admin.html.twig', array(
            'appointments_week' => $appointments_week,
            'appointments_to_delete' => $appointments_to_delete,
            'schedules' => $schedules,
            'number_week' => $number_week,
            'message' => $message,
        ));
    }




    public function takeAppointmentAction(Appointment $appointment_old, Appointment $appointment_new){
        $em = $this->getDoctrine()->getManager();

        foreach ($appointment_old->getUsers() as $user){
            $user_id = $user->getId();
            // On ajoute l'utilisateur au RDV
            $appointment_new->addUser($user);
        }
        $appointment_new->setTheme($appointment_old->getTheme());
        $appointment_new->setTitle($appointment_old->getTitle());
        $appointment_new->setCommentary($appointment_old->getCommentary());
        $appointment_new->setCommentarySeance($appointment_old->getCommentarySeance());
        $appointment_new->setCommentaryClient($appointment_old->getCommentaryClient());
        $em->persist($appointment_new);

        // On supprime les rendez-vous autour de celui pri
        $this->removeAppointmentsAround($appointment_new);

        $em->remove($appointment_old);
        $em->flush();



        if ($this->isGranted("ROLE_ADMIN")){
            return $this->redirectToRoute("user_historic", array("id" => $user_id));
        }else{
            return $this->redirectToRoute("monCompte_index");
        }
    }



    public function changeAppointmentAction(Appointment $appointment_old, $number_week){

        //Début sécurité
        $flag = false;
        foreach ($appointment_old->getUsers() as $user){
            if($this->getUser() == $user){
                $flag = true;
                break;
            }
        }
        if ($flag == false or !$this->isGranted("ROLE_ADMIN")){
            $this->denyAccessUnlessGranted('ROLE_ADMIN', null, 'Désolé petit malin, vous n\'avez pas accès à cette page !');
        }
        //Fin sécurité


        $em = $this->getDoctrine()->getManager();

        $appointments_week = $this->getAppointmentsByWeek($number_week);


        $schedules = $em->getRepository('SophrologieBundle:Schedule')->findAllOrderByTime();

        return $this->render('appointment/changeAppointment.html.twig', array(
            'appointments_week' => $appointments_week,
            'appointment_old' => $appointment_old,
            'schedules' => $schedules,
            'number_week' => $number_week,
        ));

    }

    public function confirmChangeAppointmentAction(Appointment $appointment_old, Appointment $appointment_new){

        //Début sécurité
        $flag = false;
        foreach ($appointment_old->getUsers() as $user){
            if($this->getUser() == $user){
                $flag = true;
                break;
            }
        }
        if ($flag == false or !$this->isGranted("ROLE_ADMIN")){
            $this->denyAccessUnlessGranted('ROLE_ADMIN', null, 'Désolé petit malin, vous n\'avez pas accès à cette page !');
        }
        //Fin sécurité


        //On change le RDV

        $em = $this->getDoctrine()->getManager();

        foreach ($appointment_old->getUsers() as $user){
            $appointment_new->addUser($user);
            $appointment_old->removeUser($user);
            $em->flush($appointment_old);
            $em->flush($appointment_new);
        }

        /*
        //On ajoute les RDV autour de l'ancien
        $interval_old = new \DateInterval('PT30M');

        $last_appointment_old = new Appointment;
        $next_appointment_old = new Appointment;
        $last_appointment_date = $appointment_old->getDate()->sub($interval_old);
        $last_appointment_old->setDate($last_appointment_date);
        //$next_appointment_old->setDate($appointment_old->getDate()->add($interval_old)->add($interval_old));

        $em->flush($last_appointment_old);
        $em->flush($last_appointment_old);
*/

        //On supprime les rendez-vous autour du nouveau RDV
        $this->removeAppointmentsAround($appointment_new);


        //On envoie un mail à l'utilisateur
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

        //On envoie un mail à l'admin

        if ($this->isGranted("ROLE_ADMIN")){
            return $this->redirectToRoute("appointment_admin");
        }
        else {
            return $this->redirectToRoute("appointment_index");
        }

    }

    /*
     * Index de l'utilisateur connecté
     */

    public function userAction(){
        $em = $this->getDoctrine()->getManager();

        $themes = $em->getRepository('SophrologieBundle:Theme')->findByUser($this->getUser());

        $tableau_appointments = array();

        $num_theme = 0;
        foreach ($themes as $theme){
            $num_appointment = 0;
            $appointments = $em->getRepository('SophrologieBundle:Appointment')->findByTheme($theme);
            foreach ($appointments as $appointment){
                $tableau[$num_theme][$num_appointment] = $appointment;
                $num_appointment++;
            }
            $num_theme++;
        }

        return $this->render('user/historic.html.twig', array(
            'tableau' => $tableau_appointments,
        ));
    }

    public function addSeanceAction(Request $request, Theme $theme){

        $form = $this->createForm('SophrologieBundle\Form\SeanceType');
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();

            $appointment = new Appointment();
            $appointment->setTitle($form['title']->getData());
            $appointment->setCommentarySeance($form['commentarySeance']->getData());
            $appointment->setTheme($theme);
            $appointment->setDate(null);
            $appointment->addUser($theme->getUser());
            $em->persist($appointment);
            $em->flush();

            return $this->redirectToRoute('user_historic', array('id' => $theme->getUser()->getId()));
        }

        return $this->render('appointment/seance.html.twig', array(
            'form' => $form->createView(),
            'theme' => $theme,
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
