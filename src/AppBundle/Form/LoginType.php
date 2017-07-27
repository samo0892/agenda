<?php
/**
 * Created by PhpStorm.
 * User: samo
 * Date: 13.04.17
 * Time: 10:37
 */

namespace AppBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Validator\Constraints as Assert;

class LoginType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('user', TextType::class, array(
                'constraints' => array(
                    new Assert\NotBlank(array(
                        'message' => 'Feld darf nicht leer nicht sein'
                    ))
                )
            ))
            /*array (
                'message' => 'The value is not a valid.',))
             )) */
            ->add('password', PasswordType::class, array(
                'constraints' => array(
                    new Assert\NotBlank(array(
                        'message' => 'Feld darf nicht leer nicht sein'
                    ))
                )
            ))

            ->add('save', SubmitType::class, array('label' => 'Login'));

    }
}