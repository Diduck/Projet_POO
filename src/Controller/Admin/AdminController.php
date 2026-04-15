<?php

namespace App\Controller\Admin;

use App\Repository\BookRepository;
use App\Repository\CategoryRepository;
use App\Repository\LoanRepository;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin')]
#[IsGranted('ROLE_ADMIN')]
class AdminController extends AbstractController
{
    #[Route('', name: 'admin_dashboard')]
    public function index(
        BookRepository $bookRepo,
        CategoryRepository $catRepo,
        UserRepository $userRepo,
        LoanRepository $loanRepo
    ): Response {
        return $this->render('admin/dashboard.html.twig', [
            'totalBooks' => count($bookRepo->findAll()),
            'availableBooks' => count($bookRepo->findAvailable()),
            'totalCategories' => count($catRepo->findAll()),
            'totalUsers' => count($userRepo->findAll()),
            'activeLoans' => $loanRepo->countActive(),
            'recentLoans' => $loanRepo->findBy([], ['dateEmprunt' => 'DESC'], 5),
        ]);
    }
}
