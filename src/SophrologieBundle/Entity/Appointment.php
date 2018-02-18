<?php

namespace SophrologieBundle\Entity;

/**
 * Appointment
 */
class Appointment
{
    /**
     * @var int
     */
    private $id;

    /**
     * @var string
     */
    private $commentary;


    /**
     * Get id
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set commentary
     *
     * @param string $commentary
     *
     * @return Appointment
     */
    public function setCommentary($commentary)
    {
        $this->commentary = $commentary;

        return $this;
    }

    /**
     * Get commentary
     *
     * @return string
     */
    public function getCommentary()
    {
        return $this->commentary;
    }
    /**
     * @var \SophrologieBundle\Entity\AppointmentType
     */
    private $appointmentType;

    /**
     * @var \SophrologieBundle\Entity\Timeslot
     */
    private $timeslot;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $users;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->users = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Set appointmentType
     *
     * @param \SophrologieBundle\Entity\AppointmentType $appointmentType
     *
     * @return Appointment
     */
    public function setAppointmentType(\SophrologieBundle\Entity\AppointmentType $appointmentType = null)
    {
        $this->appointmentType = $appointmentType;

        return $this;
    }

    /**
     * Get appointmentType
     *
     * @return \SophrologieBundle\Entity\AppointmentType
     */
    public function getAppointmentType()
    {
        return $this->appointmentType;
    }

    /**
     * Set timeslot
     *
     * @param \SophrologieBundle\Entity\Timeslot $timeslot
     *
     * @return Appointment
     */
    public function setTimeslot(\SophrologieBundle\Entity\Timeslot $timeslot = null)
    {
        $this->timeslot = $timeslot;

        return $this;
    }

    /**
     * Get timeslot
     *
     * @return \SophrologieBundle\Entity\Timeslot
     */
    public function getTimeslot()
    {
        return $this->timeslot;
    }

    /**
     * Add user
     *
     * @param \SophrologieBundle\Entity\User $user
     *
     * @return Appointment
     */
    public function addUser(\SophrologieBundle\Entity\User $user)
    {
        $this->users[] = $user;

        return $this;
    }

    /**
     * Remove user
     *
     * @param \SophrologieBundle\Entity\User $user
     */
    public function removeUser(\SophrologieBundle\Entity\User $user)
    {
        $this->users->removeElement($user);
    }

    /**
     * Get users
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getUsers()
    {
        return $this->users;
    }

    public function __toString()
    {
        return (string) $this->date;
    }

    /**
     * @var \SophrologieBundle\Entity\Theme
     */
    private $theme;


    /**
     * Set theme
     *
     * @param \SophrologieBundle\Entity\Theme $theme
     *
     * @return Appointment
     */
    public function setTheme(\SophrologieBundle\Entity\Theme $theme = null)
    {
        $this->theme = $theme;

        return $this;
    }

    /**
     * Get theme
     *
     * @return \SophrologieBundle\Entity\Theme
     */
    public function getTheme()
    {
        return $this->theme;
    }
    /**
     * @var string
     */
    private $commentary_client;


    /**
     * Set commentaryClient
     *
     * @param string $commentaryClient
     *
     * @return Appointment
     */
    public function setCommentaryClient($commentaryClient)
    {
        $this->commentary_client = $commentaryClient;

        return $this;
    }

    /**
     * Get commentaryClient
     *
     * @return string
     */
    public function getCommentaryClient()
    {
        return $this->commentary_client;
    }
    /**
     * @var string
     */
    private $commentaryClient;


    /**
     * @var \DateTime
     */
    private $date;


    /**
     * Set date
     *
     * @param \DateTime $date
     *
     * @return Appointment
     */
    public function setDate($date)
    {
        $this->date = $date;

        return $this;
    }

    /**
     * Get date
     *
     * @return \DateTime
     */
    public function getDate()
    {
        return $this->date;
    }
    /**
     * @var \DateTime
     */
    private $taking;


    /**
     * Set taking
     *
     * @param \DateTime $taking
     *
     * @return Appointment
     */
    public function setTaking($taking)
    {
        $this->taking = $taking;

        return $this;
    }

    /**
     * Get taking
     *
     * @return \DateTime
     */
    public function getTaking()
    {
        return $this->taking;
    }
    /**
     * @var string
     */
    private $title;

    /**
     * @var string
     */
    private $commentary_seance;


    /**
     * Set title
     *
     * @param string $title
     *
     * @return Appointment
     */
    public function setTitle($title)
    {
        $this->title = $title;

        return $this;
    }

    /**
     * Get title
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Set commentarySeance
     *
     * @param string $commentarySeance
     *
     * @return Appointment
     */
    public function setCommentarySeance($commentarySeance)
    {
        $this->commentary_seance = $commentarySeance;

        return $this;
    }

    /**
     * Get commentarySeance
     *
     * @return string
     */
    public function getCommentarySeance()
    {
        return $this->commentary_seance;
    }
}
