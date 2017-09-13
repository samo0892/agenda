<?php

namespace AppBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

class StartedMeetingType extends AbstractType
{
   public function buildForm(FormBuilderInterface $builder, array $options) 
   {
       $builder
            ->add('notice', CollectionType::class, array(
                         'entry_type' => NoticeType::class,
                         'allow_add' => true,
                         'allow_delete' => true,
                         'prototype' => true,
                         'label' => false,
                     ))
            ->add('save', SubmitType::class, array('label' => 'Speichern&Senden'));
   }
}

