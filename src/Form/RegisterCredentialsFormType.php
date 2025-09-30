<?php

namespace App\Form;

use App\Entity\RegisterCredentials;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class RegisterCredentialsFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('Username', TextType::class, [
                'required' => true,
                'attr' =>
                    [
                        'placeholder' => 'Enter username',
                        'class' => 'form-control',
                        'style' => "margin: 5px"
                    ]
            ])
            ->add('Password', PasswordType::class, [
                'required' => true,
                'attr' =>
                    [
                        'placeholder' => 'Enter username',
                        'class' => 'form-control',
                        'style' => "margin: 5px"
                    ]
            ])
            ->add('Register', SubmitType::class, [
                'attr' => [
                    'class' => 'btn btn-success',
                    'style' => "margin: 5px"
                ]
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => RegisterCredentials::class,
        ]);
    }
}
