<?php

namespace AppBundle\Service;

use AppBundle\Entity\File;

class DetailService
{
    public function getDetails($form, $meeting, $meeting_files)
    {
        $file = new File();
        $fileNames = [];
        $form->get('name')->setData($meeting->getName());
        $form->get('date')->setData($meeting->getDate());
        $form->get('startTime')->setData($meeting->getStarttime());
        $form->get('endTime')->setData($meeting->getEndtime());
        $form->get('place')->setData($meeting->getPlace());
        $form->get('emails')->setData($meeting->getEmails());
        $form->get('type')->setData($meeting->getType());
        foreach ($meeting_files as $meeting_file){
            $file->getName($meeting_file);
            
        }
//            dump($fileNames);
        foreach($fileNames as $fileName){
         $file = $fileName->getName();  
         dump($file);
         $fileArray[] = $file;
            
    }die;$form->get('file')->setData($file);}
    
    public function updateDetails($form, $meeting)
    {
        $newData = $meeting;
        $newData->setName($form->get('name')->getData());
        $newData->setDate($form->get('date')->getData());
        $newData->setStarttime($form->get('startTime')->getData());
        $newData->setEndtime($form->get('endTime')->getData());
        $newData->setPlace($form->get('place')->getData());
        $newData->setObjective($form->get('emails')->getData());
        $newData->setIsAttending($form->get('type')->getData());
        $newData->setFile($form->get('file')->getData());

        $em = $this->getDoctrine()->getManager();
        $em->persist($meeting);
        $em->flush();
    }
}