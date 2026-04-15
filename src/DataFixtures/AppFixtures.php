<?php

namespace App\DataFixtures;

use App\Entity\Book;
use App\Entity\Category;
use App\Entity\Loan;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    public function __construct(private UserPasswordHasherInterface $hasher)
    {
    }

    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create('fr_FR');

        $categoryNames = [
            'Roman' => 'Fiction narrative avec des personnages et des intrigues.',
            'Science-Fiction' => 'Romans futuristes et technologiques.',
            'Histoire' => 'Livres sur les grands événements historiques.',
            'Biographie' => 'Récits de vie de personnages célèbres.',
            'Informatique' => 'Ouvrages techniques sur la programmation.',
            'Philosophie' => 'Textes de réflexion philosophique.',
            'Art' => 'Livres sur la peinture, la sculpture et les arts visuels.',
            'Sciences' => 'Ouvrages scientifiques et vulgarisation.',
        ];

        $categories = [];
        foreach ($categoryNames as $nom => $description) {
            $cat = new Category();
            $cat->setNom($nom)->setDescription($description);
            $manager->persist($cat);
            $categories[] = $cat;
        }

        $admin = new User();
        $admin->setEmail('admin@biblio.fr')
            ->setRoles(['ROLE_ADMIN'])
            ->setPassword($this->hasher->hashPassword($admin, 'admin123'));
        $manager->persist($admin);

        $users = [$admin];
        $userEmails = [
            'alice.martin@example.com',
            'bob.dupont@example.com',
            'claire.petit@example.com',
            'david.leroy@example.com',
            'emma.bernard@example.com',
        ];
        foreach ($userEmails as $email) {
            $user = new User();
            $user->setEmail($email)
                ->setPassword($this->hasher->hashPassword($user, 'user123'));
            $manager->persist($user);
            $users[] = $user;
        }

        $bookData = [
            ['Le Grand Voyage', 'Marie Dupont', 0],
            ['Les Étoiles Mourantes', 'Jean-Pierre Martin', 1],
            ['La Révolution Française', 'Pierre Legrand', 2],
            ['Vie de Napoléon', 'Sophie Blanc', 3],
            ['Python pour les Nuls', 'Thomas Girard', 4],
            ['Méditations', 'Antoine Rousseau', 5],
            ["Histoire de l'Art Moderne", 'Claire Morel', 6],
            ['La Physique Quantique', 'Luc Petit', 7],
            ["L'Énigme du Temps", 'Isabelle Roux', 0],
            ['Cosmos 2050', 'Marc Lefebvre', 1],
            ['Seconde Guerre Mondiale', 'Nathalie Garnier', 2],
            ['Steve Jobs, une vie', 'Julien Faure', 3],
            ['JavaScript Moderne', 'Amélie Renard', 4],
            ["L'Être et le Néant", 'Christophe Fleury', 5],
            ['Impressionnisme', 'Virginie Dupuis', 6],
            ["L'ADN expliqué", 'Mathieu Chevallier', 7],
            ['Les Misérables', 'Victor Hugo', 0],
            ['Fondation', 'Isaac Asimov', 1],
            ['La Chute de Rome', 'Bernard Arnaud', 2],
            ["Darwin, l'évolution", 'Patricia Laurent', 3],
            ['Docker et Kubernetes', 'Sébastien Hubert', 4],
            ['La République', 'François Mercier', 5],
            ['Picasso', 'Delphine Simon', 6],
            ['Relativité Générale', 'Nicolas Perrin', 7],
            ['Un Monde Meilleur', 'Céline Fontaine', 0],
            ['Neuromancien', 'Xavier Guillot', 1],
            ['Les Croisades', 'Stéphane Henry', 2],
            ['Mémoires de Churchill', 'Aurélie Robin', 3],
            ['Symfony 6 par la pratique', 'Vincent Boulanger', 4],
            ['Éthique à Nicomaque', 'Camille Denis', 5],
        ];

        $books = [];
        foreach ($bookData as $idx => $data) {
            $book = new Book();
            $resumeParts = $faker->sentences(3);
            $resume = implode(' ', $resumeParts);
            if (strlen($resume) < 20) {
                $resume .= ' Un ouvrage incontournable à lire absolument.';
            }

            $book->setTitre($data[0])
                ->setAuteur($data[1])
                ->setResume($resume)
                ->setDatePublication($faker->dateTimeBetween('-50 years', '-1 year'))
                ->setDisponible($idx % 5 !== 0)
                ->setCategory($categories[$data[2]]);
            $manager->persist($book);
            $books[] = $book;
        }

        $regularUsers = array_slice($users, 1);
        $unavailableBooks = array_values(array_filter($books, fn($b) => !$b->isDisponible()));

        foreach ($unavailableBooks as $book) {
            $dateEmprunt = $faker->dateTimeBetween('-45 days', '-5 days');
            $dateRetour = (clone $dateEmprunt)->modify('+14 days');

            $statut = $dateRetour < new \DateTime('today')
                ? Loan::STATUS_EN_RETARD
                : Loan::STATUS_EN_COURS;

            $loan = new Loan();
            $loan->setBook($book)
                ->setUser($faker->randomElement($regularUsers))
                ->setDateEmprunt($dateEmprunt)
                ->setDateRetourPrevue($dateRetour)
                ->setStatut($statut);
            $manager->persist($loan);
        }

        $availableBooks = array_values(array_filter($books, fn($b) => $b->isDisponible()));
        $historyBooks = array_slice($availableBooks, 0, 8);

        foreach ($historyBooks as $book) {
            $dateEmprunt = $faker->dateTimeBetween('-90 days', '-30 days');
            $dateRetour = (clone $dateEmprunt)->modify('+14 days');

            $loan = new Loan();
            $loan->setBook($book)
                ->setUser($faker->randomElement($regularUsers))
                ->setDateEmprunt($dateEmprunt)
                ->setDateRetourPrevue($dateRetour)
                ->setStatut(Loan::STATUS_RENDU);
            $manager->persist($loan);
        }

        $manager->flush();
    }
}
