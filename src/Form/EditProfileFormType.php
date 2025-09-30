<?php

namespace App\Form;

use App\Entity\Credentials;
use App\Entity\User;
use MongoDB\Driver\Session;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class EditProfileFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('Password', PasswordType::class, [
              'required' => false,
              'attr' => [
                  'placeholder' => 'Enter new password',
                  'class' => 'form-control',
                  'style' => "margin: 5px"
              ]
            ])
            ->add('ConfirmPassword', PasswordType::class, [
                'required' => false,
                'attr' => [
                    'placeholder' => 'Confirm new password',
                    'class' => 'form-control',
                    'style' => "margin: 5px"
                ]
            ])
            ->add('ProfilePictureUpload', FileType::class, [
                'required' => false,
                'attr' =>
                    [
                        'placeholder' => 'Upload profile picture',
                        'class' => 'form-control',
                        'style' => "margin: 5px"
                    ]
            ])
            ->add('Update', SubmitType::class, [
                'attr' =>
                    [
                        'class' => 'btn btn-success',
                        'style' => "margin: 5px"
                    ]
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => null,
        ]);
    }
}
