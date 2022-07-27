<?php

namespace App\Form;

use App\Entity\Campus;
use App\Entity\Etat;
use App\Entity\Lieu;
use App\Entity\Sortie;
use App\Entity\Ville;
use App\Repository\VilleRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\PropertyAccess\PropertyAccess;

class SortieType extends AbstractType
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager) {
        $this->entityManager = $entityManager;
    }
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom', TextType::class, [
                'label' => 'Nom de la sortie :',
            ])
            ->add('dateHeureDebut', DateTimeType::class, [
                'label' => 'Date et heure de la sortie :',
                'html5' => false,
                'data' => new \DateTime(),
                'format' => 'dd MM yyyy'

            ])
            ->add('dateLimiteInscription', DateTimeType::class, [
                'label' => 'Date limite d\'inscription :',
                'html5' => false,
                'data' => new \DateTime(),
                'format' => 'dd MM yyyy'
            ])
            ->add('nbInscriptionsMax', IntegerType::class, [
                'label' => 'Nombre de places :',
            ])
            ->add('duree', IntegerType::class, [
                'label' => 'Durée',
            ])
            ->add('infosSortie', TextareaType::class, [
                'label' => 'Description et infos :',
            ])
            ->add('campus', EntityType::class, [
                'label' => 'Campus :',
                'class' => Campus::class,
                'choice_label' => 'nom',
            ])
            ->add('ville', EntityType::class, [
                'label' => 'Ville : ',
                'mapped' => false,
                'choice_label' => 'nom',
                'class' => Ville::class,
            ])
            ->addEventListener(
                FormEvents::POST_SET_DATA,
                function (FormEvent $event) {
                    $form = $event->getForm();
                    $data = $event->getData();
                    $form->add('lieu', EntityType::class, [
                        'label' => 'Lieu:',
                        'class' => Lieu::class,
                        'choice_label' => 'nom',
                        'choices' => $data->getLieu()->getVille()->getLieus()
                    ])
                        ->add('rue', TextType::class, [
                            'label' => 'Address:',
                            'mapped' => false,
                            'disabled' => true,
                        ])
                        ->get('rue')->setData($data->getLieu()->getRue());
                    $form
                        ->add('codePostal', TextType::class, [
                            'label' => 'Code Postal:',
                            'mapped' => false,
                            'disabled' => true,
                        ])
                        ->get('codePostal')->setData($data->getLieu()->getVille()->getCodePostal());
                    $form
                        ->add('latitude', TextType::class, [
                            'label' => 'Latitude:',
                            'mapped' => false,
                            'disabled' => true,
                        ])
                        ->get('latitude')->setData($data->getLieu()->getLatitude());
                    $form
                        ->add('longitude', TextType::class, [
                            'label' => 'Longitude:',
                            'mapped' => false,
                            'disabled' => true,
                        ])
                        ->get('longitude')->setData($data->getLieu()->getLongitude());

                })
            ->get('ville')->addEventListener(
                FormEvents::POST_SUBMIT,
                function (FormEvent $event) {
                    $form = $event->getForm();
                    $id = $event->getData();
                    $form->getParent()->add('lieu', EntityType::class, [
                        'label' => 'Lieu:',
                        'class' => Lieu::class,
                        'choice_label' => 'nom',
                        'query_builder' => function (EntityRepository $er) use ($id) {
                            return $er->createQueryBuilder('l')
                                ->where('l.ville = :val')
                                ->setParameter('val', $id);
                        }
                    ]);
                    $fullData = $this->entityManager->getRepository(Lieu::class);
                    $data = $fullData->find($form->getParent()->get('lieu')->getData()->getId());
                    dump($data);
                    dump($data->getRue());
                    dump($form->getParent());
                    $form->getParent()->get('rue')->setData($data->getRue());
                    $form->getParent()
                        ->remove('codePostal')
                        ->add('codePostal', TextType::class, [
                            'label' => 'Code Postal:',
                            'mapped' => false,
                            'disabled' => true,
                        ])
                        ->get('codePostal')
                        ->setData($data->getVille()->getCodePostal());
                    $form->getParent()->get('latitude')->setData($data->getLatitude());
                    $form->getParent()->get('longitude')->setData($data->getLongitude());
                    dump($form->getParent());

                }
            );

    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Sortie::class,
        ]);
    }
}
