<?php

namespace App\Form;

use App\Entity\Campus;
use App\Entity\Lieu;
use App\Entity\Sortie;
use App\Entity\Ville;
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

class SortieType extends AbstractType
{
    private EntityManagerInterface $entityManager;

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
            ->add('lieu', TextType::class, [
                'mapped' => false,
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
                            //dump($newLieu->getQuery()->getFirstResult());
                        }
                    ]);
                    $ville = $this->entityManager->getRepository(Ville::class)->findBy(['id' => $id]);
//                    dump($ville);
//                    dump($id);
//                    dump($form->getParent()->get('ville'));
                    $data = $this->entityManager->getRepository(Lieu::class)->findBy(['ville' => $ville]);
                    //$data = $fullData->find($form->getParent()->get('lieu')->getData()->getId());

                    $form->getParent()->get('rue')->setData($data[0]->getRue());
                    $form->getParent()
                        ->add('codePostal', TextType::class, [
                            'label' => 'Code Postal:',
                            'mapped' => false,
                            'disabled' => true,
                        ])
                        ->get('codePostal')
                        ->setData($data[0]->getVille()->getCodePostal());
                    $form->getParent()->get('latitude')->setData($data[0]->getLatitude());
                    $form->getParent()->get('longitude')->setData($data[0]->getLongitude());

//                    dump($data);
//                    dump($form->getParent()->get('lieu'));

                }
            )

        ;

    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Sortie::class,
        ]);
    }
}
