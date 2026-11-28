<?php

namespace App\Controller;

use App\Entity\Borrowing;
use App\Entity\Reservation;
use App\Form\BorrowingType;
use App\Repository\BorrowingRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/borrowing')]
final class BorrowingController extends AbstractController
{
    #[Route(name: 'app_borrowing_index', methods: ['GET'])]
    public function index(BorrowingRepository $borrowingRepository): Response
    {
        return $this->render('borrowing/index.html.twig', [
            'borrowings' => $borrowingRepository->findBy([], ['id' => 'DESC']),
        ]);
    }

    #[Route('/new', name: 'app_borrowing_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $borrowing = new Borrowing();
        $borrowing->setBorrowedAt(new \DateTimeImmutable('today'));
        $borrowing->setDueDate(new \DateTimeImmutable('+14 days'));

        $form = $this->createForm(BorrowingType::class, $borrowing);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $book = $borrowing->getBook();

            if ($book->getQuantity() < 1) {
                // Show the error on the form instead of redirecting, so the input is kept
                $form->get('book')->addError(new FormError('No copy of this book is available.'));
            } else {
                $book->setQuantity($book->getQuantity() - 1);

                if ($reservation = $borrowing->getReservation()) {
                    $reservation->setStatus(Reservation::STATUS_FULFILLED);
                }

                $entityManager->persist($borrowing);
                $entityManager->flush();

                $this->addFlash('success', 'Borrowing created.');

                return $this->redirectToRoute('app_borrowing_index', [], Response::HTTP_SEE_OTHER);
            }
        }

        return $this->render('borrowing/new.html.twig', [
            'borrowing' => $borrowing,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_borrowing_show', methods: ['GET'])]
    public function show(Borrowing $borrowing): Response
    {
        return $this->render('borrowing/show.html.twig', [
            'borrowing' => $borrowing,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_borrowing_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Borrowing $borrowing, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(BorrowingType::class, $borrowing);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_borrowing_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('borrowing/edit.html.twig', [
            'borrowing' => $borrowing,
            'form' => $form,
        ]);
    }

    #[Route('/{id}/return', name: 'app_borrowing_return', methods: ['POST'])]
    public function return(Request $request, Borrowing $borrowing, EntityManagerInterface $entityManager): Response
    {
        if (!$this->isCsrfTokenValid('return'.$borrowing->getId(), $request->getPayload()->getString('_token'))) {
            $this->addFlash('danger', 'Invalid security token.');

            return $this->redirectToRoute('app_borrowing_index', [], Response::HTTP_SEE_OTHER);
        }

        if ($borrowing->getReturnedAt() === null) {
            $borrowing->setReturnedAt(new \DateTimeImmutable());
            $book = $borrowing->getBook();
            $book->setQuantity($book->getQuantity() + 1);
            $entityManager->flush();

            $this->addFlash('success', 'Book returned.');
        }

        return $this->redirectToRoute('app_borrowing_index', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('/{id}', name: 'app_borrowing_delete', methods: ['POST'])]
    public function delete(Request $request, Borrowing $borrowing, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$borrowing->getId(), $request->getPayload()->getString('_token'))) {
            // Deleting an active borrowing puts the copy back in stock
            if ($borrowing->getReturnedAt() === null) {
                $book = $borrowing->getBook();
                $book->setQuantity($book->getQuantity() + 1);
            }

            $entityManager->remove($borrowing);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_borrowing_index', [], Response::HTTP_SEE_OTHER);
    }
}