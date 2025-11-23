<?php

namespace App\Form;

use App\Entity\Configuration;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ConfigurationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('maxLivres', IntegerType::class, [
                'label' => 'Nombre maximum de livres par membre',
                'attr' => ['min' => 1]
            ])
            ->add('dureeEmprunt', IntegerType::class, [
                'label' => 'Durée d\'un emprunt (en jours)',
                'attr' => ['min' => 1]
            ])
            ->add('montantPenalite', MoneyType::class, [
                'label' => 'Pénalité par jour de retard',
                'currency' => 'EUR',
                'required' => false
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Configuration::class,
        ]);
    }
}
