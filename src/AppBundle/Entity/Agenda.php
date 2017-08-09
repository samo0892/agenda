<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;


/**
 * @ORM\Entity
 * @ORM\Table(name="agenda")
 */
class Agenda
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
     * @var integer
     * 
     * @ORM\Column(type="smallint")
     */
    private $minutes;

    /**
     * @var Meeting     
     * @ORM\ManyToOne(targetEntity="Meeting", inversedBy="agendas")
     * @ORM\JoinColumn(name="meeting_id", referencedColumnName="id", onDelete="set null")
     */
    private $meeting;
    
    /**
     * @return Meeting
     */
    function getMeeting() {
        return $this->meeting;
    }
    
    /**
     * @parameter Meeting $meeting
     */
    function setMeeting($meeting) {
        $this->meeting = $meeting;
    }

    function getName() {
        return $this->name;
    }

    function getMinutes() {
        return $this->minutes;
    }

    function setName($name) {
        $this->name = $name;
    }

    function setMinutes($minutes) {
        $this->minutes = $minutes;
    }
}
