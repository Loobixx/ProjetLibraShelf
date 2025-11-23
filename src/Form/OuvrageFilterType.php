<?php

namespace App\Form;

use App\Entity\Auteur;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class OuvrageFilterType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('query', TextType::class, [
                'label' => false,
                'required' => false,
                'attr' => ['placeholder' => 'Titre...', 'class' => 'form-control']
            ])

            ->add('auteur', EntityType::class, [
                'class' => Auteur::class,

                // On demande à Doctrine de trier par Nom (ASC = Croissant)
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('a')
                        ->orderBy('a.nom', 'ASC');
                },

                'choice_label' => function (Auteur $auteur) {
                    return $auteur->getNom() . ' ' . $auteur->getPrenom();
                },
                'label' => false,
                'required' => false,
                'placeholder' => 'Auteur',
                'attr' => ['class' => 'form-select']
            ])

            ->add('categorie', TextType::class, [
                'label' => false,
                'required' => false,
                'attr' => ['placeholder' => 'Catégorie', 'class' => 'form-control']
            ])
            ->add('langue', TextType::class, [
                'label' => false,
                'required' => false,
                'attr' => ['placeholder' => 'Langue', 'class' => 'form-control']
            ])
            ->add('annee', IntegerType::class, [
                'label' => false,
                'required' => false,
                'attr' => ['placeholder' => 'Année', 'class' => 'form-control']
            ])
            ->add('disponible', CheckboxType::class, [
                'label' => 'Uniquement disponible',
                'required' => false,
                'attr' => ['class' => 'form-check-input']
            ])
            ->add('sort', ChoiceType::class, [
                'label' => false,
                'required' => false,
                'choices' => [
                    'Titre (A-Z)' => 'titre_asc',
                    'Titre (Z-A)' => 'titre_desc',
                    'Année (Récent)' => 'annee_desc',
                    'Année (Ancien)' => 'annee_asc',
                ],
                'placeholder' => 'Trier par...',
                'attr' => ['class' => 'form-select']
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'method' => 'GET',
            'csrf_protection' => false,
        ]);
    }

    public function getBlockPrefix(): string
    {
        return '';
    }
}
