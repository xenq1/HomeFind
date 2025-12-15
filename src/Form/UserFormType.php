<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormEvent;

class UserFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $isEdit = $options['is_edit'] ?? false;

        $builder
            ->add('username', TextType::class, [
                'label' => 'Username',
                'required' => true,
                'attr' => ['class' => 'w-full border rounded-lg p-2'],
                'constraints' => [
                    new Assert\NotBlank(['message' => 'Username is required']),
                    new Assert\Length([
                        'min' => 3,
                        'minMessage' => 'Username must be at least 3 characters',
                    ]),
                ],
            ])
            ->add('plainPassword', PasswordType::class, [
                'label' => $isEdit ? 'New Password (leave blank to keep current)' : 'Password',
                'required' => !$isEdit,
                'attr' => ['class' => 'w-full border rounded-lg p-2'],
                'mapped' => false,
                'constraints' => $isEdit ? [] : [
                    new Assert\NotBlank(['message' => 'Password is required']),
                    new Assert\Length([
                        'min' => 6,
                        'minMessage' => 'Password must be at least 6 characters',
                    ]),
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $isEdit = $resolver->getDefinedOptions()['is_edit'] ?? false;
        
        $resolver->setDefaults([
            'data_class' => User::class,
            'is_edit' => false,
            'by_reference' => false,
            'validation_groups' => function ($form) {
                $isEdit = $form->getConfig()->getOption('is_edit');
                return $isEdit ? ['Default'] : ['Registration', 'Default'];
            },
        ]);
    }
}
