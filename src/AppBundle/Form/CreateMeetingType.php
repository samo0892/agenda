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
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
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
                    'placeholder' => array(
                        'day' => 'Tag', 'month' => 'Monat', 'year' => 'Jahr'
                    ),
                    'constraints' => array(
                        new Assert\NotBlank(array(
                            'message' => 'Feld darf nicht leer nicht sein'
                                ))
                    )
                ))
                ->add('startTime', TimeType::class, array('label' => 'Startzeit',
                    'constraints' => array(
                        new Assert\NotBlank(array(
                            'message' => 'Feld darf nicht leer nicht sein'
                                ))
                    )
                ))
                ->add('endTime', TimeType::class, array('label' => 'Endzeit',
                    'constraints' => array(
                        new Assert\NotBlank(array(
                            'message' => 'Feld darf nicht leer nicht sein'
                                ))
                    )
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
                            'message' => 'Feld darf nicht leer nicht sein'
                                ))
                    )
                ))
                ->add('type',ChoiceType::class, array(
                    'choices'  => array(
                        'Sitzung' => 'Sitzung',
                        'Telefonkonferenz' => 'Telefonkonferenz',
                        'Videokonferenz' => 'Videokonferenz',
                        'Stehung' => 'Stehung'
                    ),
                ))
                ->add('agendas', CollectionType::class, array(
                    'entry_type' => AgendaType::class,
//                    'entry_options' => array('label' => false),
                    'allow_add' => true,
                    'allow_delete' => true,
                    'prototype' => true,
                ))
                ->add('file', FileType::class, array('label' => 'Datei hinzufügen'))

//            ->add('isAttending', ChoiceType::class, array('label' => 'Art des Meetings',
//                'choices'  => array(
//                    'Sitzung' => 'Null',
//                    'Telefonkonferenz' => 'true',
//                    'Videokonferenz' => 'false',
//                )
//            ))    
                ->add('save', SubmitType::class, array('label' => 'Meeting erstellen'));
    }

    public function configureOptions(OptionsResolver $resolver) {
        $resolver->setDefaults(array(
            'data_class' => Meeting::class,
        ));
    }

}
