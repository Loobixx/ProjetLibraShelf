<?php

namespace App\Form;

use App\Entity\Auteur;
use App\Entity\Ouvrage;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class OuvrageType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('titre')
            ->add('editeur')
            ->add('annee', IntegerType::class)
            ->add('ISBN')
            ->add('resume', TextareaType::class, [
                'required' => false,
            ])
            ->add('langues')
            ->add('categories')
            ->add('tags')

            ->add('auteurs', EntityType::class, [
                'class' => Auteur::class,

                // Tri alphabétique (A-Z)
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('a')
                        ->orderBy('a.nom', 'ASC');
                },

                'choice_label' => function (Auteur $auteur) {
                    return $auteur->getNom() . ' ' . $auteur->getPrenom();
                },

                'multiple' => true,
                'expanded' => true,
                'label' => false,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Ouvrage::class,
        ]);
    }
}
