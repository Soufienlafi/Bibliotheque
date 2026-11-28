<?php

namespace App\Form;

use App\Entity\Book;
use App\Entity\Reservation;
use App\Entity\User;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

class ReservationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('user', EntityType::class, [
                'class' => User::class,
                'choice_label' => fn (User $u) => sprintf('%s (%s)', $u->getFullName(), $u->getStudentNumber()),
                'placeholder' => 'Select a member',
                'constraints' => [new NotBlank(message: 'Please select a member')],
            ])
            ->add('book', EntityType::class, [
                'class' => Book::class,
                'choice_label' => 'title',
                'placeholder' => 'Select a book',
                'constraints' => [new NotBlank(message: 'Please select a book')],
            ])
            ->add('reservedAt', DateType::class, [
                'label' => 'Reserved on',
                'widget' => 'single_text',
                'input' => 'datetime_immutable',
            ])
            ->add('expiresAt', DateType::class, [
                'label' => 'Expires on',
                'widget' => 'single_text',
                'input' => 'datetime_immutable',
            ])
            ->add('status', ChoiceType::class, [
                'choices' => Reservation::STATUSES,
                'constraints' => [new NotBlank()],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(['data_class' => Reservation::class]);
    }
}