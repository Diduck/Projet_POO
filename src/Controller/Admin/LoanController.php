<?php

namespace App\Controller\Admin;

use App\Entity\Loan;
use App\Form\LoanType;
use App\Repository\LoanRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin/loans')]
#[IsGranted('ROLE_ADMIN')]
class LoanController extends AbstractController
{
    #[Route('', name: 'admin_loans_list')]
    public function index(LoanRepository $loanRepo): Response
    {
        return $this->render('admin/loan/index.html.twig', [
            'loans' => $loanRepo->findBy([], ['dateEmprunt' => 'DESC']),
        ]);
    }

    #[Route('/{id}/edit', name: 'admin_loans_edit', methods: ['GET', 'POST'], requirements: ['id' => '\d+'])]
    public function edit(Loan $loan, Request $request, EntityManagerInterface $em): Response
    {
        $form = $this->createForm(LoanType::class, $loan);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // If loan is returned, make book available again
            if ($loan->getStatut() === Loan::STATUS_RENDU) {
                $loan->getBook()->setDisponible(true);
            } else {
                $loan->getBook()->setDisponible(false);
            }
            $em->flush();
            $this->addFlash('success', 'Emprunt mis à jour avec succès.');
            return $this->redirectToRoute('admin_loans_list');
        }

        return $this->render('admin/loan/edit.html.twig', [
            'loan' => $loan,
            'form' => $form,
        ]);
    }

    #[Route('/{id}/delete', name: 'admin_loans_delete', methods: ['POST'], requirements: ['id' => '\d+'])]
    public function delete(Loan $loan, Request $request, EntityManagerInterface $em): Response
    {
        if ($this->isCsrfTokenValid('delete' . $loan->getId(), $request->request->get('_token'))) {
            // Make the book available again when loan is deleted
            $loan->getBook()->setDisponible(true);
            $em->remove($loan);
            $em->flush();
            $this->addFlash('success', 'Emprunt supprimé avec succès.');
        } else {
            $this->addFlash('danger', 'Token CSRF invalide.');
        }

        return $this->redirectToRoute('admin_loans_list');
    }
}
