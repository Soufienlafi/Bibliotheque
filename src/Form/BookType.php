<?php

namespace App\Form;

use App\Entity\Author;
use App\Entity\Book;
use App\Entity\Category;
use App\Entity\Publisher;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Isbn;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\PositiveOrZero;

class BookType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title', TextType::class, [
                'label' => 'Title',
                'attr' => ['placeholder' => 'e.g. The Little Prince'],
                'constraints' => [
                    new NotBlank(message: 'Please enter a title'),
                    new Length(max: 255),
                ],
            ])
            ->add('isbn', TextType::class, [
                'label' => 'ISBN',
                'help' => '10 or 13 digits, hyphens allowed.',
                'attr' => ['placeholder' => '978-3-16-148410-0'],
                'constraints' => [
                    new NotBlank(message: 'Please enter the ISBN'),
                    new Isbn(),
                ],
            ])
            ->add('description', TextareaType::class, [
                'label' => 'Description',
                'attr' => ['rows' => 5],
                'constraints' => [
                    new NotBlank(message: 'Please enter a description'),
                ],
            ])
            ->add('publicationDate', DateType::class, [
                'label' => 'Publication date',
                'widget' => 'single_text',
                'constraints' => [
                    new NotBlank(message: 'Please select a date'),
                ],
            ])
            ->add('quantity', IntegerType::class, [
                'label' => 'Copies available',
                'attr' => ['min' => 0],
                'constraints' => [
                    new NotBlank(),
                    new PositiveOrZero(message: 'Quantity cannot be negative'),
                ],
            ])
            ->add('category', EntityType::class, [
                'class' => Category::class,
                'choice_label' => 'name',
                'placeholder' => 'Select a category',
                'required' => false,
            ])
            ->add('publisher', EntityType::class, [
                'class' => Publisher::class,
                'choice_label' => 'name',
                'placeholder' => 'Select a publisher',
                'required' => false,
            ])
            ->add('authors', EntityType::class, [
                'class' => Author::class,
                'choice_label' => 'fullName',
                'multiple' => true,
                'by_reference' => false,
                'attr' => ['size' => 6],
                'help' => 'Hold Ctrl (or Cmd) to select several authors.',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Book::class,
        ]);
    }
}