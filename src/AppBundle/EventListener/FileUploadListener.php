<?php

namespace AppBundle\EventListener;

use Symfony\Component\HttpFoundation\File\UploadedFile;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use AppBundle\Entity\Meeting;
use AppBundle\FileUploader;

class FileUploadListener
{
    private $uploader;
            
    public function __construct(FileUploader $uploader)
    {
        $this->uploader = $uploader;
    }
    
    public function prePersist(LifecycleEventArgs $args)
    {
        $entity = $args->getEntity();
        
        $this->uploadFile($entity);
    }
    
    public function preUpdate(PreUpdateEventArgs $args)
    {
        $entity = $args->getEntity();
        
        $this->uploadFile($entity);
    }
    
    public function uploadFile($entity)
    {
        if(!$entity instanceof Meeting){
            return;
        }
        
        $file = $entity->getFile();
        
        if(!$file instanceof UploadedFile){
            return;
        }
        
        $fileName = $this->uploader->upload($file);
        $entity->setFile($fileName);
    }
}

