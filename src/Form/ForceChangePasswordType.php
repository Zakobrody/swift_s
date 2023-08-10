<?php

namespace App\Form;

use App\Constraint\UniquePassword;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Regex;

class ForceChangePasswordType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('password', RepeatedType::class, [
                'type' => PasswordType::class,
                'invalid_message' => 'Hasła muszą być takie same',
                'options' => ['attr' => ['class' => 'password-field']],
                'required' => true,
                'first_options'  => ['label' => 'Nowe hasło'],
                'second_options' => ['label' => 'Powtórz hasło'],
                'constraints' => [
                    new NotBlank([
                        'message' => 'Podaj hasło',
                    ]),
                    new Regex([
                        'pattern' => '/^(?=.*[A-Z]){2,}(?=.*[a-z]){2,}(?=.*[0-9]){2,}(?=.*[!@#$%^&*]){2,}[a-zA-Z0-9!@#$%^&*]{8,}$/',
                        'message' => 'Hasło musi zawierać co najmniej 8 znaków. W tym min.: 2 małe litery, 2 duże litery, 2 cyfry i 2 znaki specjalne.',
                    ]),
                    new UniquePassword([
                        'field'       => 'password',
                    ]),
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            // Configure your form options here
        ]);
    }
}
