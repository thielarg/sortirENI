<?php

namespace App\Form;

use App\Entity\Participant;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;

class ParticipantType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('pseudo', TextType::class, [
                'label' => 'Pseudo* :'
            ])
            ->add('prenom', TextType::class, [
                'label' => 'Prénom* :'
            ])
            ->add('nom', TextType::class, [
                'label' => 'Nom* :'
            ])
            ->add('telephone', TextType::class, [
                'label' => 'Téléphone* :'
            ])
            ->add('email', TextType::class, [
                'label' => 'Email* :'
            ])
            ->add('password', RepeatedType::class, [
                'type' => PasswordType::class,
                'invalid_message' => 'Vous n\'avez pas saisi le même mot de passe',
                'first_options' => [
                    'label' => 'Mot de passe* :'
                ],
                'second_options' => [
                    'label' => 'Confirmation* :'
                ]
            ])
            ->add('site', EntityType::class,
                ['class' => 'App\Entity\Site',
                    'label' => 'Ville de rattachement* :',
                    'choice_label' => 'nom',
                    'placeholder' => '-- Choisir un site',
                    'required' => true,
                ])
            ->add('photo', FileType::class, [
                'label' => 'Photo (PNG, JPG, BMP) :',
                'mapped' => false,
                'required' => false,
                'constraints' => [
                    new File([
                        'maxSize' => '1024k',
                        'mimeTypes' => [
                            'image/png',
                            'image/jpg',
                            'image/bmp',
                        ],
                        'mimeTypesMessage' => 'Merci de sélectionner un fichier PNG, JPG, BMP valide.',
                        'maxSizeMessage' => 'La taille du fichier ne peut pas dépasser 1024 Ko',
                    ])
                ],
            ])

        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Participant::class,
        ]);
    }
}
