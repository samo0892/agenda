<?php

namespace AppBundle\Service;

class DetailService
{
    public function getDetails($form, $meeting)
    {
        $form->get('name')->setData($meeting->getName());
        $form->get('date')->setData($meeting->getDate());
        $form->get('startTime')->setData($meeting->getStarttime());
        $form->get('endTime')->setData($meeting->getEndtime());
        $form->get('place')->setData($meeting->getPlace());
        $form->get('emails')->setData($meeting->getEmails());
        $form->get('type')->setData($meeting->getType());
        $form->get('file')->setData($meeting->getFile());
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
        $newData->setFile($form->get('file')->getData());

        $em = $this->getDoctrine()->getManager();
        $em->persist($meeting);
        $em->flush();
    }
}