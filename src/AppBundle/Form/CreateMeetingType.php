<?php

/**
 * Created by PhpStorm.
 * User: samo
 * Date: 19.04.17
 * Time: 13:44
 */

namespace AppBundle\Form;

use Symfony\Component\Form\Extension\Core\Type\TimeType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use AppBundle\Entity\Meeting;
use AppBundle\Form\AgendaType;

class CreateMeetingType extends AbstractType {

    public function buildForm(FormBuilderInterface $builder, array $options) {
        $builder
                ->add('name', TextType::class, array('label' => 'Thema des Meetings',
                    'constraints' => array(
                        new Assert\NotBlank(array(
                            'message' => 'Feld darf nicht leer nicht sein'
                                ))
                    ),
                    'data' => ''
                ))
                ->add('date', DateType::class, array('label' => 'Datum',
                    'widget' => 'single_text',
                    'html5' => false,
                    'attr' => ['class' => 'js-datepicker form-control'],
                    'format' => 'mm-dd-yyyy',
                ))
                ->add('startTime', TimeType::class, array('label' => 'Startzeit',
                    'constraints' => array(
                        new Assert\NotBlank(array(
                            'message' => 'Feld darf nicht leer nicht sein'
                                ))
                    ),
                    'widget' => 'choice',
                    'hours' => ['7','8','9','10','11','12','13','14','15','16','1', '18'],
                    'minutes' => ['0', '5', '10', '15', '20', '25', '30', '35', '40', '45', '50', '55']
                ))
                ->add('endTime', TimeType::class, array('label' => 'Endzeit',
                    'constraints' => array(
                        new Assert\NotBlank(array(
                            'message' => 'Feld darf nicht leer nicht sein'
                                ))
                    ), 
                    'widget' => 'choice',
                    'hours' => ['7','8','9','10','11','12','13','14','15','16','1', '18'],
                    'minutes' => ['0', '5', '10', '15', '20', '25', '30', '35', '40', '45', '50', '55']
                ))
                ->add('place', TextType::class, array('label' => 'Ort',
                    'constraints' => array(
                        new Assert\NotBlank(array(
                            'message' => 'Feld darf nicht leer nicht sein'
                                ))
                    )
                ))
                ->add('emails', TextType::class, array('label' => 'Teilnehmer',
                    'constraints' => array(
                        new Assert\NotBlank(array(
                            'message' => 'Feld darf nicht leer sein'
                                ))
                    )
                ))
                ->add('type',ChoiceType::class, array('label' => 'Art des Meetings',
                    'choices'  => array(
                        'Sitzung' => 'Sitzung',
                        'Telefonkonferenz' => 'Telefonkonferenz',
                        'Videokonferenz' => 'Videokonferenz',
                        'Stehung' => 'Stehung'
                    ),
                ))
                
                ->add('description', TextareaType::class, array('label' => 'Ziele des Meetings',
                    'constraints' => array(
                        new Assert\NotBlank(array(
                            'message' => 'Feld darf nicht leer sein'
                        ))
                    )
                ))
                
                ->add('agendas', CollectionType::class, array(
                    'entry_type' => AgendaType::class,
                    'allow_add' => true,
                    'allow_delete' => true,
                    'prototype' => true,
                    'label' => false,
                ))
                
                ->add('files', FileType::class, array('label' => 'Datei hinzufügen',
                    'required' => false,
                    'multiple' => true,
                ))
   
                ->add('save', SubmitType::class, array('label' => 'Meeting erstellen'));
    }

    public function configureOptions(OptionsResolver $resolver) {
        $resolver->setDefaults(array(
            'data_class' => Meeting::class,
        ));
    }

}
