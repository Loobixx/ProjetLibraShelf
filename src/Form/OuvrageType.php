<?php

namespace App\Form;

use App\Entity\Auteur;
use App\Entity\Ouvrage;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class OuvrageType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('titre')
            ->add('editeur')
            ->add('ISBN')
            ->add('categories')
            ->add('tags')
            ->add('langues')
            ->add('annee')
            ->add('resume')
            ->add('auteurs', EntityType::class, [
                'class' => Auteur::class,
                'choice_label' => 'id',
                'multiple' => true,
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
