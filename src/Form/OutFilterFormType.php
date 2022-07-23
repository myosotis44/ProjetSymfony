<?php

namespace App\Form;

use App\Entity\Campus;
use App\Form\Model\OutFilterFormModel;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;


class OutFilterFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('outFilterCampus', EntityType::class, [
                'class' => Campus::class,
                'choice_label' => 'nom',
                'label' => 'Campus'
            ])
            ->add('outFilterSearch', TextType::class, [
                'label' => 'Le nom de la sortie contient :'
            ])
            ->add('outFilterStartDate', DateType::class, [
                'label' => 'Entre'
            ])
            ->add('outFilterEndDate', DateType::class, [
                'label' => 'et'
            ])
           ->add('outFilterChk', ChoiceType::class, [
                'label' => ' ',
                'choices' => [
                    'Sorties dont je suis l\'organisateur/trice' => 'ChkOrg',
                    'Sorties auxquelles je suis inscrit/e' => 'ChkSub',
                    'Sorties auxquelles je ne suis pas inscrit/e' => 'ChkNotSub',
                    'Sorties passées' => 'ChkEnd',
                ],
                'multiple' => true,
                'expanded' => true
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            // Configure your form options here
            'data_class' => OutFilterFormModel::class
        ]);
    }
}
