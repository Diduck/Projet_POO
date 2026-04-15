<?php

namespace App\Controller;

use App\Entity\Book;
use App\Entity\Loan;
use App\Repository\BookRepository;
use App\Repository\CategoryRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class BookController extends AbstractController
{
    #[Route('/books', name: 'app_books')]
    public function index(Request $request, BookRepository $bookRepo, CategoryRepository $catRepo): Response
    {
        $categoryId = $request->query->get('category');
        $search = $request->query->get('q');

        if ($search) {
            $books = $bookRepo->findByTitre($search);
        } elseif ($categoryId) {
            $books = $bookRepo->findByCategory((int) $categoryId);
        } else {
            $books = $bookRepo->findBy([], ['titre' => 'ASC']);
        }

        $categories = $catRepo->findAll();

        return $this->render('book/index.html.twig', [
            'books' => $books,
            'categories' => $categories,
            'selectedCategory' => $categoryId,
            'search' => $search,
        ]);
    }

    #[Route('/books/{id}', name: 'app_book_show', requirements: ['id' => '\d+'])]
    public function show(Book $book): Response
    {
        return $this->render('book/show.html.twig', [
            'book' => $book,
        ]);
    }

    #[Route('/books/{id}/borrow', name: 'app_book_borrow', methods: ['POST'], requirements: ['id' => '\d+'])]
    #[IsGranted('ROLE_USER')]
    public function borrow(Book $book, EntityManagerInterface $em): Response
    {
        if (!$book->isDisponible()) {
            $this->addFlash('warning', 'Ce livre n\'est pas disponible à l\'emprunt.');
            return $this->redirectToRoute('app_book_show', ['id' => $book->getId()]);
        }

        $loan = new Loan();
        $loan->setBook($book)
            ->setUser($this->getUser())
            ->setDateEmprunt(new \DateTime())
            ->setDateRetourPrevue(new \DateTime('+14 days'))
            ->setStatut(Loan::STATUS_EN_COURS);

        $book->setDisponible(false);

        $em->persist($loan);
        $em->flush();

        $this->addFlash('success', sprintf(
            'Emprunt enregistré ! Date de retour prévue : %s',
            $loan->getDateRetourPrevue()->format('d/m/Y')
        ));

        return $this->redirectToRoute('app_loans');
    }
}
