<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

class UserType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('username', TextType::class, ['label' => 'Nom d\'utilisateur', 'empty_data' => ''])
            ->add('password', RepeatedType::class, [
                'type' => PasswordType::class,
                'invalid_message' => 'Les deux mots de passe doivent correspondre.',
                'required' => true,
                'first_options' => ['label' => 'Mot de passe', 'empty_data' => ''],
                'second_options' => ['label' => 'Tapez le mot de passe à nouveau', 'empty_data' => ''],
                'data' => ['', ''],
            ])
            ->add('email', EmailType::class, ['label' => 'Adresse email', 'empty_data' => ''])
            ->add('roles', ChoiceType::class, [
                'label' => 'Role',
                'choices' => [
                    'Utilisateur' => 'ROLE_USER',
                    'Administrateur' => 'ROLE_ADMIN',
                ],
                'expanded' => true,
                'multiple' => false,
            ])
        ;

        $builder->get('roles')
            ->addModelTransformer(new CallbackTransformer(
                function ($rolesArray) {
                    $role = 'ROLE_USER';
                    if (\in_array('ROLE_ADMIN', $rolesArray, true)) {
                        $role = 'ROLE_ADMIN';
                    }

                    return $role;
                },
                function ($rolesString) {
                    return [$rolesString];
                }
            ))
        ;
    }
}
