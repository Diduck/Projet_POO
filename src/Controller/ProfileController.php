<?php

namespace App\Controller;

use App\Repository\LoanRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_USER')]
class ProfileController extends AbstractController
{
    #[Route('/profile', name: 'app_profile')]
    public function index(LoanRepository $loanRepo): Response
    {
        $loans = $loanRepo->findByUser($this->getUser());

        return $this->render('profile/index.html.twig', [
            'user'  => $this->getUser(),
            'loans' => $loans,
        ]);
    }

    #[Route('/loans', name: 'app_loans')]
    public function loans(LoanRepository $loanRepo): Response
    {
        $loans = $loanRepo->findByUser($this->getUser());

        return $this->render('profile/loans.html.twig', [
            'loans' => $loans,
        ]);
    }
}
