<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraint as Assert;

/**
 * @ORM\Entity
 * @ORM\Table(name="started_meeting")
 */
class StartedMeeting
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
     * @ORM\Column(type="string", length=256)
     */
    private $notice;
    
    /**
     * @var string
     * 
     * @ORM\Column(type="string", length=50)
     */
    private $type;
    
    /**
     * @var string
     * 
     * @ORM\Column(type="string", length=50)
     */
    private $person;
    
    /**
     * @var \DateTime
     * 
     * @ORM\Column(type="date", length=50)
     */
    private $date;
    
    /**
     * @var Meeting     
     * @ORM\ManyToOne(targetEntity="Meeting", inversedBy="notice")
     * @ORM\JoinColumn(name="meeting_id", referencedColumnName="id", onDelete="set null")
     */
    private $meeting;
    
    function getMeeting() {
        return $this->meeting;
    }

    function setMeeting(Meeting $meeting) {
        $this->meeting = $meeting;
    }

        
    function getId() {
        return $this->id;
    }

    function getNotice() {
        return $this->notice;
    }

    function getType() {
        return $this->type;
    }

    function getPerson() {
        return $this->person;
    }

    function getDate() {
        return $this->date;
    }

    function setNotice($notice) {
        $this->notice = $notice;
    }

    function setType($type) {
        $this->type = $type;
    }

    function setPerson($person) {
        $this->person = $person;
    }

    function setDate(\DateTime $date) {
        $this->date = $date;
    }


}

