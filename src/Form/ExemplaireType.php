<?php
// src/Form/ExemplaireType.php

namespace App\Form;

use App\Entity\Exemplaire;
use App\Entity\Ouvrage;
use Symfony\Component\Form\AbstractType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ExemplaireType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('cote')
            ->add('etat', ChoiceType::class, [
                'choices' => [
                    'Neuf' => 'Neuf',
                    'Bon' => 'Bon',
                    'Usagé' => 'Usage',
                    'Endommagé' => 'Endommage',
                    'Hors-service' => 'Hors-service',
                ],
                'placeholder' => 'Choisir un état',
            ])
            ->add('ouvrage', EntityType::class, [
                'class' => Ouvrage::class,
                'choice_label' => 'titre',
                'placeholder' => 'Choisir un ouvrage'
            ]);
        ;
    }

    // ...
}
