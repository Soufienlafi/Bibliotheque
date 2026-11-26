<?php

namespace App\Form;

use App\Entity\Author;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class AuthorType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('fullName', TextType::class, [
                'label' => 'Full name',
                'attr' => ['placeholder' => 'e.g. Antoine de Saint-Exupéry'],
                'constraints' => [
                    new NotBlank(message: 'Please enter the author\'s name'),
                    new Length(
                        min: 2,
                        max: 100,
                        minMessage: 'The name should be at least {{ limit }} characters',
                        maxMessage: 'The name cannot be longer than {{ limit }} characters',
                    ),
                ],
            ])
            ->add('biography', TextareaType::class, [
                'label' => 'Biography',
                'required' => false,
                'attr' => [
                    'rows' => 6,
                    'placeholder' => 'Short biography (optional)',
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Author::class,
        ]);
    }
}