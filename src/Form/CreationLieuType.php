<?php

namespace App\Form;

use App\Entity\Lieu;
use App\Entity\Ville;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CreationLieuType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom', TextType::class, [
                'label' => 'Nom du lieu: '
            ])
            ->add('rue', TextType::class, [
                'label' => 'Rue: '
            ])
            ->add('latitude', NumberType::class, [
                'label' => 'Latitude: '
            ])
            ->add('longitude', NumberType::class, [
                'label' => 'Longitude: '
            ])
            ->add('ville', EntityType::class, [
                'label' => 'Ville: ',
                'choice_label' => 'nom',
                'class' => Ville::class
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Lieu::class,
        ]);
    }
}
