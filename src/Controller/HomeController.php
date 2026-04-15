<?php

namespace App\Controller;

use App\Repository\BookRepository;
use App\Repository\CategoryRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class HomeController extends AbstractController
{
    #[Route('/', name: 'app_home')]
    public function index(BookRepository $bookRepo, CategoryRepository $catRepo): Response
    {
        $recentBooks = $bookRepo->findBy(['disponible' => true], ['id' => 'DESC'], 6);
        $categories = $catRepo->findAll();

        return $this->render('home/index.html.twig', [
            'recentBooks' => $recentBooks,
            'categories' => $categories,
        ]);
    }
}
