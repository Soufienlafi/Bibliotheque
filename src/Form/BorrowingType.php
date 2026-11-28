<?php

namespace App\Form;

use App\Entity\Book;
use App\Entity\Borrowing;
use App\Entity\Reservation;
use App\Entity\User;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

class BorrowingType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $current = $builder->getData()?->getReservation();

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
            ->add('reservation', EntityType::class, [
                'class' => Reservation::class,
                'required' => false,
                'placeholder' => 'No reservation',
                'help' => 'Only reservations not yet converted into a borrowing are listed.',
                'choice_label' => fn (Reservation $r) => sprintf(
                    '#%d - %s (%s)',
                    $r->getId(),
                    $r->getBook()?->getTitle(),
                    $r->getUser()?->getFullName()
                ),
                'query_builder' => function (EntityRepository $er) use ($current) {
                    $qb = $er->createQueryBuilder('r')
                        ->leftJoin('r.borrowing', 'b')
                        ->where('b.id IS NULL');
                    if ($current) {
                        $qb->orWhere('r = :current')->setParameter('current', $current);
                    }
                    return $qb->orderBy('r.id', 'DESC');
                },
            ])
            ->add('borrowedAt', DateType::class, [
                'label' => 'Borrowed on',
                'widget' => 'single_text',
                'input' => 'datetime_immutable',
            ])
            ->add('dueDate', DateType::class, [
                'label' => 'Due date',
                'widget' => 'single_text',
                'input' => 'datetime_immutable',
            ])
            /**->add('returnedAt', DateType::class, [
                'label' => 'Returned on',
                'widget' => 'single_text',
                'input' => 'datetime_immutable',
                'required' => false,
                'help' => 'Leave empty while the book is still out.',
            ])*/
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(['data_class' => Borrowing::class]);
    }
}