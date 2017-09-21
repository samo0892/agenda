<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;


/**
 * @ORM\Entity
 * @ORM\Table(name="meetings")
 */
class Meeting
{
    /**
     * @var int
     *
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     * 
     * @ORM\Column(type="string", length=50)
     */
    private $name;

    /**
     * @var \DateTime
     *
     * @ORM\Column(type="datetime")
     */
    private $date;

    /**
     * @var time
     * 
     * @ORM\Column(type="time")
     */
    private $startTime;
    
    /**
     * @var time
     * 
     * @ORM\Column(type="time")
     */
    private $endTime;

    /**
     * @ORM\Column(type="string", length=50)
     */
    private $place;

    /**
     * @var boolean
     * 
     * * @ORM\Column(type="boolean")
     */
    private $isComplete = '0';
    
    /**
     * @var string
     * 
     * @ORM\Column(type="string", length=50)
     */
    private $type;
    
    /**
     * @var string
     * 
     * @ORM\Column(type="string", length=800)
     */
    private $description;
    
    /**
     * @var string
     * 
     * @ORM\Column(type="text")
     */
    private $emails;
    
    /**
     * @ORM\OneToMany(targetEntity="File", mappedBy="meeting", cascade={"persist"})
     *
     * @var array
     */
    private $files;
    
    /**
     * A list of agendas by this meeting.
     *
     * @var array
     *
     * @ORM\OneToMany(targetEntity="Agenda", mappedBy="meeting", cascade={"persist"})
     */
    private $agendas;
    
    /**
     * A list of agendas by this meeting.
     *
     * @var array
     *
     * @ORM\OneToMany(targetEntity="StartedMeeting", mappedBy="meeting", cascade={"persist"})
     */
    private $notice;
    
    /**
     * @ORM\Column(type="string", length=255)
     *
     * @Assert\File
     */
    private $pdfFile;
    
    public function __construct() {
        
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param $name
     */
    public function setName($name)
    {
        $this->name = $name;
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
    
    public function getDescription()
    {
        return $this->description;
    }
    
    public function setDescription($description)
    {
        $this->description = $description;
    }
    
    /** Returns the list of files
     *
     * @return array
     */
    public function getFile()
    {
        return $this->files;
    }
    
    /**
     * 
     * @param array $files
     * @return $this
     */
    public function setFile($files)
    {
        $this->files = $files;
        return $this;
    }
    
    /** Returns the list of agendas
     *
     * @return array
     */
    public function getAgendas()
    {
        return $this->agendas;
    }

    /**
     * Set the list of agendas
     *
     * @param array $agendas
     */
    public function setAgendas($agendas)
    {
        $this->agendas = $agendas;
    }
    
    function getStartTime() {
        return $this->startTime;
    }

    function getEndTime() {
        return $this->endTime;
    }

    function getType() {
        return $this->type;
    }

    function setStartTime($startTime) {
        $this->startTime = $startTime;
    }

    function setEndTime($endTime) {
        $this->endTime = $endTime;
    }

    function setType($type) {
        $this->type = $type;
    }
    
    function getNotice() {
        return $this->notice;
    }

    function setNotice($notice) {
        $this->notice = $notice;
    }

    function getPdfFile() {
        return $this->pdfFile;
    }

    function setPdfFile($pdfFile) {
        $this->pdfFile = $pdfFile;
    }



}