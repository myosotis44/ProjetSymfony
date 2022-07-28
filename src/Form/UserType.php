<?php

namespace App\Form;

use App\Entity\Campus;
use App\Entity\Participant;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\TelType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class UserType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('pseudo', TextType::class, [
                'label' => 'Pseudo: '
            ])
            ->add('prenom', TextType::class, [
                'label' => 'Prénom: '
            ])
            ->add('nom', TextType::class, [
                'label' => 'Nom: '
            ])
            ->add('telephone', TelType::class, [
                'label' => 'Téléphone: '
            ])
            ->add('mail', EmailType::class, [
                'label' => 'Email: '
            ])

            ->add('password', PasswordType::class, [
                'label' => 'Mot de passe: ',
                'mapped' => false,
                'attr' => ['autocomplete' => 'new-password'],
                'constraints' => [
                    new NotBlank([
                        'message'=>'Entrez un mot de passe s\'il vous plait'
                    ]),
                    new Length([
                        'min'=> 5,
                        'minMessage'=> 'Le mot de passe doit faire au moins 5 caractères.',
                        'max'=>100
                    ])
                ]
            ])
            ->add('confirmation', PasswordType::class, [
                'label' => 'Confirmation: ',
                'mapped' => false,
                'attr' => ['autocomplete' => 'new-password'],
                'constraints' => [
                    new NotBlank([
                        'message'=>'Entrez un mot de passe s\'il vous plait'
                    ]),
                    new Length([
                        'min'=> 4,
                        'minMessage'=> 'Le mot de passe doit faire au moins 5 caractères.',
                        'max'=>100
                    ])
                ]
            ])
            ->add('campus', EntityType::class, [
                'label' => 'Campus: ',
                'class' => Campus::class,
                'choice_label' => 'nom'
            ])
            ->add('imageFile', FileType::class, [
                'label' => 'Image de profil: ',
                'mapped' =>false,
                'required' => false,
                'constraints' => [
                    new File([
                        'mimeTypes' => [
                            'image/*',
                        ],
                        'mimeTypesMessage' => 'Téléchargez une image valide s\'il vous plait',
                    ])
                ]
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
