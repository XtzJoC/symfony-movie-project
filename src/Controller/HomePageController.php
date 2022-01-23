<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\Persistence\ManagerRegistry;
use App\Entity\Movie;

class HomePageController extends AbstractController
{
    #[Route('/', name: 'home_page')]
    public function getAll(ManagerRegistry $doctrine): Response
    {
        $entityManager = $doctrine->getManager();
        $movies = $entityManager->getRepository(Movie::class)->findAllOrdered();

        return $this->render('home_page/index.html.twig', [
            'movies' => $movies,
        ]);
    }
}
