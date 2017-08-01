<?php

namespace AppBundle\Service;

/**
 * This service renders and sends a mail to the subscriber of a meeting
 */

class EmailService
{
    private $templating;
    private $mailer;
    private $sendfrom;
    private $sendto;
    
    public function __construct($templating, $mailer, $sendfrom, $sendto)
    {
        $this->templating = $templating;
        $this->mailer = $mailer;
        $this->sendfrom = $sendfrom;
        $this->sendto = $sendto;
    }
    
    /**
     * Creates and sends the mail to the subscribers
     * 
     * @param type $subject
     * @param type $mailBody
     * @return boolean
     */
    public function sendHtmlEmail($subject, $mailBody, $sendto/*, $fileName*/)
    {
        $message = \Swift_Message::newInstance()
                ->setSubject($subject)
                ->setFrom($this->sendfrom)
                ->setTo($sendto)
                ->setBody($mailBody);
//                ->attach(\Swift_Attachment::fromPath($fileName));
        
        $mailer = $this->mailer->send($message);
        
        //checks if the mail is successful sent
        if($mailer){
            return true;
        }else{
            return false;
        }                       
    }
    
    /**
     * Renders the mail with the input of the user
     * 
     * @param type $form
     * @param type $templatePath
     * @return type
     */
    public function renderHtmlMail($form, $templatePath)
    {
        $meeting_name = $form["meeting_name"]->getData();
        $date = $form["date"]->getData();
        $time = $form["time"]->getData();
        $place = $form["place"]->getData();
        $objective = $form["objective"]->getData();
        $isAttending = $form["isAttending"]->getData();
        $file = $form["file"]->getData();
        
        $params = array(
            'meeting_name' => $meeting_name,
            'date' => $date,
            'time' => $time,
            'place' => $place,
            'objective' => $objective,
            'isAttending' => $isAttending,
            'file' => $file
        );
        
        //creates the mail with the inputs of the user and the given template
        $mailBody = $this->templating->render(
                $templatePath,
                $params,
                'text/html');
        
        return $mailBody;
    }
}

