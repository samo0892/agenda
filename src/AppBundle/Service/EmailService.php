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
    public function sendHtmlEmail($subject, $mailBody, $sendto, $fileName)
    {
        $recipients = preg_split("/[;,]+/", $sendto);
        
        foreach ($recipients as $recipient) {
            $message = \Swift_Message::newInstance()
                    ->setSubject($subject)
                    ->setFrom($this->sendfrom)
                    ->setTo($recipient)
                    ->setBody($mailBody)
                    ->attach(\Swift_Attachment::fromPath($fileName, 'text/calendar'));

            $mailer = $this->mailer->send($message);
        }
        
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
        $meeting_name = $form["name"]->getData();
        $date = $form["date"]->getData();
        $startTime = $form["startTime"]->getData();
        $endTime = $form["endTime"]->getData();
        $place = $form["place"]->getData();
        $objective = $form["emails"]->getData();
        $isAttending = $form["type"]->getData();
        $file = $form["file"]->getData();
        $description = $form["description"]->getData();
        
        $params = array(
            'meeting_name' => $meeting_name,
            'date' => $date,
            'startTime' => $startTime,
            'endTime' => $endTime,
            'place' => $place,
            'objective' => $objective,
            'type' => $isAttending,
            'file' => $file,
            'description' => $description   
        );
        
        //creates the mail with the inputs of the user and the given template
        $mailBody = $this->templating->render(
                $templatePath,
                $params,
                'text/html');
        
        return $mailBody;
    }
    
    public function sendEmailToParticipants($form, $templatePath, $tmpFolder, $filePath, $fileName) {
        $mailBody = $this->renderHtmlMail($form, $templatePath, $filePath);
        $mailBody .= "<a href='$filePath'>" . $fileName . "</a>";
        $subject = "Ein neues Meeting wurde erstellt";
        $sendTo = $form->get('emails')->getData();

        $sendThisMail = $this->sendHtmlEmail($subject, $mailBody, $sendTo, $tmpFolder);
        
        return $sendThisMail;
    }
}

