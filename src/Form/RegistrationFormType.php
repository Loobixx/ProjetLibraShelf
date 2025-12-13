<?php

namespace App\Form;

use App\Entity\Personne;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\IsTrue;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\PasswordStrength;

class RegistrationFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('email')

            ->add('agreeTerms', CheckboxType::class, [
                'mapped' => false,
                'constraints' => [
                    new IsTrue([
                        'message' => 'You should agree to our terms.',
                    ]),
                ],
            ])

            ->add('plainPassword', RepeatedType::class, [
                'type' => PasswordType::class,
                'mapped' => false,
                'invalid_message' => 'Les mots de passe doivent être identiques.',
                'required' => true,

                'first_options'  => [
                    'label' => 'Mot de passe',

                    // 1. Le message d'aide qui s'affichera sous le champ
                    'help' => 'Votre mot de passe doit contenir au moins 12 caractères, comprenant des majuscules, minuscules, chiffres et caractères spéciaux.',

                    // 2. Attributs HTML5 pour aider le navigateur (validation client)
                    'attr' => [
                        'autocomplete' => 'new-password',
                        'placeholder' => 'Ex: J\'aime_Symfony_6!',
                        'minlength' => 12, // Bloque la validation si < 12 caractères
                    ],
                ],

                'second_options' => [
                    'label' => 'Confirmer le mot de passe',
                    'attr' => ['autocomplete' => 'new-password'],
                ],

                'constraints' => [
                    new NotBlank(['message' => 'Veuillez entrer un mot de passe']),
                    new PasswordStrength([
                        'minScore' => PasswordStrength::STRENGTH_STRONG,
                        'message' => 'Le mot de passe est trop faible. Veuillez suivre les instructions ci-dessous.',
                    ]),
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Personne::class,
        ]);
    }
}
