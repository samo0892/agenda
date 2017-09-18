<?php

namespace AppBundle\Form;

use Symfony\Component\Form\Extension\Core\Type\TimeType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use AppBundle\Entity\StartedMeeting;

class NoticeType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options) 
    {
        $builder
                ->add('notice', TextareaType::class, array('label' => 'Notiz'))
                ->add('type', ChoiceType::class, array('label' => 'Art',
                    'choices'  => array(
                        'Aufgabe' => 'Aufgabe',
                    ),
                ))
                ->add('person', TextType::class, array('label' => 'Wer',))
                ->add('date', DateType::class, array('label' => 'Bis',
                    'placeholder' => array(
                        'day' => 'Tag', 'month' => 'Monat', 'year' => 'Jahr'
                    ),
                    'format' => 'ddMMyyyy',));
    }
    
    public function configureOptions(OptionsResolver $resolver) {
        $resolver->setDefaults(array(
            'data_class' => StartedMeeting::class,
        ));
    }
}