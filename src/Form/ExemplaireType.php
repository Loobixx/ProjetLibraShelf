<?php

namespace App\Form;

use App\Entity\Exemplaire;
use App\Enum\EtatOuvrage;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EnumType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ExemplaireType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('cote')
            ->add('etat', EnumType::class, [
                'class' => EtatOuvrage::class,
                'label' => 'État du livre',

                'choice_label' => function ($choice) {
                    return $choice->getLabel();
                },
            ])
            ->add('disponible', CheckboxType::class, [
                'label'    => 'Cet exemplaire est-il disponible immédiatement ?',
                'required' => false,
                'help'     => 'Décochez si le livre est en réparation ou mis de côté.',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Exemplaire::class,
        ]);
    }
}
