<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\Movie;

class MovieController extends AbstractController
{
    #[Route('/movie', name: 'movie')]
    public function index(): Response
    {
        return $this->render('movie/index.html.twig', [
            'controller_name' => 'MovieController',
        ]);
    }

    #[Route('/create-movie', name: 'create-movie')]
    public function createMovie(ManagerRegistry $doctrine): Response
    {
        $entityManager = $doctrine->getManager();

        $movie = new Movie();
        $movie->setName('Test123');

        // tell Doctrine you want to (eventually) save the Movie (no queries yet)
        $entityManager->persist($movie);

        // actually executes the queries (i.e. the INSERT query)
        $entityManager->flush();

        return new Response('Saved new product with id '.$movie->getId());
    }

    #[Route('/movie/{id}', name: 'get-movie')]
    public function show(Movie $movie): Response
    {
        if (!$movie) {
            throw $this->createNotFoundException(
                'No movie found for id '.$id
            );
        }

        return new Response('Check out this great movie: '.$movie->getName());
    }

    #[Route('/movie/edit/{id}', name: 'edit-movie')]
    public function update(ManagerRegistry $doctrine, int $id): Response
    {
        $entityManager = $doctrine->getManager();
        $movie = $entityManager->getRepository(Movie::class)->find($id);

        if (!$movie) {
            throw $this->createNotFoundException(
                'No movie found for id '.$id
            );
        }

        $movie->setName('New movie name!');
        $entityManager->flush();

        return $this->redirectToRoute('get-movie', [
            'id' => $movie->getId()
        ]);
    }
}
