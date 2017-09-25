<?php

namespace AppBundle\Service;

use AppBundle\Entity\File;
use AppBundle\Entity\Meeting;
use Symfony\Component\HttpFoundation\Response;
use Doctrine\ORM\EntityManager;

class DetailService
{
    public function __construct(EntityManager $entityManager)
    {
        $this->em = $entityManager;
    }
    
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
            $fileNames[] = $meeting_file->getName();
        }
//            dump($fileNames);
//        foreach($fileNames as $fileName){
        ($form->get('uploaded_files')->setData($fileNames));
//    }}
    

            
        
        
//        foreach($fileNames as $fileName){
            
//        }die;
    }
    
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
        $newData->setFiles($form->get('files')->getData());
        $files = $meeting->getFiles();
                //dump($files); exit;
                
                
                /**
                 * Adds and saves the uploaded file in $filePath 
                 */
                foreach($files as $file){
                    if($file){
                        $fileName = md5(uniqid()) . '.' . $file->guessExtension();
                        $file = $file->move('brochures_directory', $fileName);

                        $filePath = 'file:///' . $file->getRealPath();
                        $filePath = str_replace('\\', '/', $filePath); // Replace backslashes with forwardslashes

                        $meetingFile = new File;
                        $meetingFile->setName($fileName);
                        $meetingFile->setPath($filePath);
                        $meetingFile->setMeeting($meeting);
                        $fileArray[] = $meetingFile;
                        
                    }
                }
                if(isset($fileArray)){
                    $newData->setFiles($fileArray);
                }
                
        
        $this->em->getRepository('AppBundle:Meeting');
        $this->em->persist($meeting);
        $this->em->flush();           
    }
}