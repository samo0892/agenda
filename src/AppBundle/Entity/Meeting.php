<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;


/**
 * @ORM\Entity
 * @ORM\Table(name="skygate_meetings")
 */
class Meeting
{
    /**
     * @var int
     */
    private $meeting_id;


    /**
     * @var string
     */
    private $meeting_name;

    /**
     * @var date
     */
    private $date;

    /**
     * @var time
     */
    private $time;

    /**
     * @var string
     */
    private $place;


    /**
     * @var string
     */
    private $objective;

    /**
     * @var boolean
     */
    private $isComplete;
    
    /**
     * @var string
     */
    private $isAttending;
    
    private $emails;
    
    /**
     * @ORM\Column(type="string")
     *
     * @Assert\NotBlank(message="Please, upload the product brochure as a PDF file.")
     * @Assert\File(mimeTypes={ "application/pdf" })
     */
    private $file;
    
    /**
     *
     * @var \Doctrine\Common\Collections\ArrayCollection
     */
    private $agenda;
    
    public function __construct() {
        $this->agenda = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * @return int
     */
    public function getMeetingId()
    {
        return $this->meeting_id;
    }

    /**
     * @return string
     */
    public function getMeetingName()
    {
        return $this->meeting_name;
    }

    /**
     * @param $meeting_name
     */
    public function setMeetingName($meeting_name)
    {
        $this->meeting_name = $meeting_name;
    }

    /**
     * @return date
     */
    public function getDate()
    {
        return $this->date;
    }

    /**
     * @param $date
     */
    public function setDate($date)
    {
        $this->date = $date;
    }

    /**
     * @return time
     */
    public function getTime()
    {
        return $this->time;
    }

    /**
     * @param $time
     */
    public function setTime($time)
    {
        $this->time = $time;
    }

    /**
     * @return string
     */
    public function getPlace()
    {
        return $this->place;
    }

    /**
     * @param $place
     */
    public function setPlace($place)
    {
        $this->place = $place;
    }

    /**
     * @return string
     */
    public function getObjective()
    {
        return $this->objective;
    }

    /**
     * @param $objective
     */
    public function setObjective($objective)
    {
        $this->objective = $objective;
    }

    /**
     * @return boolean
     */
    public function getIsComplete()
    {
        return $this->isComplete;
    }

    /**
     * @param $isComplete
     */
    public function setIsComplete($isComplete)
    {
        $this->isComplete = $isComplete;
    }
    
    /**
     * @return string
     */
    public function getIsAttending()
    {
        return $this->isAttending;
    }
    
    /**
     * @param $isAttending
     */
    public function setIsAttending($isAttending) {
        $this->isAttending = $isAttending;
    }
    
    public function getEmails()
    {
        return $this->emails;
    }
    
    public function setEmails($emails)
    {
        $this->emails = $emails;
    }
    
    public function getFile()
    {
        return $this->file;
    }
    
    public function setFile($file)
    {
        $this->file = $file;
        return $this;
    }
    
    public function getAgenda()
    {
        return $this->agenda;
    }
    
    public function addAgenda($agenda)
    {
        $this->agenda->add($agenda);
        return $this;
    }
    
    public function removeAgenda($agenda) {
        $this->agenda->removeElement($agenda);
    }
}