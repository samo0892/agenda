<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;


/**
 * @ORM\Entity
 * @ORM\Table(name="file")
 */
class File
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
     * @var string
     * 
     * @ORM\Column(type="string", length=255)
     */
    private $path;

   /**
     * @var Meeting     
     * @ORM\ManyToOne(targetEntity="Meeting", inversedBy="file")
     * @ORM\JoinColumn(name="meeting_id", referencedColumnName="id", onDelete="cascade")
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

    function getPath() {
        return $this->path;
    }

    function setName($name) {
        $this->name = $name;
    }

    function setPath($path) {
        $this->path = $path;
    }
}
