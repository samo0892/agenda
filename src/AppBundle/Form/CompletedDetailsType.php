<?php

namespace AppBundle\Form;

use Symfony\Component\Form\Extension\Core\Type\TimeType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Validator\Constraints as Assert;

class CompletedDetailsType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {        
        $builder
                ->add('meeting_name', TextType::class, array('label' => 'Thema des Meetings',
                    'constraints' => array(
                        new Assert\NotBlank(array(
                            'message' => 'Feld darf nicht leer sein'
                        ))
                    )
                ))

                ->add('date', DateType::class, array('label' => 'Datum',
                    'placeholder' => array(
                        'day' => 'Tag', 'month' => 'Monat', 'year' => 'Jahr'
                    ),
                    'constraints' => array(
                        new Assert\NotBlank(array(
                            'message' => 'Feld darf nicht leer sein'
                        ))
                    )
                ))
                
                ->add('time', TimeType::class, array('label' => 'Uhrzeit',
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

            ->add('objective', EmailType::class, array('label' => 'Teilnehmer',
                'constraints' => array(
                    new Assert\NotBlank(array(
                        'message' => 'Feld darf nicht leer nicht sein'
                    ))
                )
            ))
                
            ->add('isAttending', TextType::class, array('label' => 'Art des Meetings',
                'constraints' => array(
                    new Assert\NotBlank(array(
                        'message' => 'Feld darf nicht leer sein'
                    ))
                )
            ))
                
            ->add('file', TextType::class, array('label' => 'Datei',
                'constraints' => array(
                    new Assert\NotBlank(array(
                        'message' => 'Feld darf nicht leer sein'
                    ))
                )
            ));                
    }            
}

