<?php

namespace App\Form;

use App\Entity\Credentials;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ApiTokenFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {

        $builder
            ->add('Token', TextType::class, [
                'required' => false,
                'attr' =>
                    [
                        'placeholder' => '',
                        'class' => 'form-control',
                        'disabled' => true,
                        'style' => "margin: 5px",
                        'value' => $options["token"]
                    ]
                ])
            ->add("Delete", SubmitType::class, [
                "attr" => [
                    "class" => "btn btn-danger btn-block",
                    "style" => "margin: 5px; display: inline-block",
                ]
            ])
            ->add('Refresh', SubmitType::class, [
                'attr' =>
                    [
                        'class' => 'btn btn-primary',
                        'style' => "margin: 5px; display: inline-block;",
                    ]
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => null,
            'token' => ''
        ]);
    }
}
